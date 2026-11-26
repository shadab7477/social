<?php

namespace App\Services\Inbox;

use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Enums\MessageDirection;
use App\Models\Conversation;
use App\Models\ConversationAssignment;
use App\Models\Message;
use App\Models\SocialProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SocialInboxService
{
    /**
     * Ingest an inbound comment or direct message into a unified conversation thread.
     */
    public function ingestInboundMessage(
        SocialProfile $profile,
        string $extConvId,
        ConversationType $type,
        string $senderName,
        string $senderId,
        ?string $senderAvatar,
        string $extMsgId,
        string $body,
        ?array $attachments = null,
        ?Carbon $sentAt = null
    ): Message {
        return DB::transaction(function () use (
            $profile, $extConvId, $type, $senderName, $senderId, $senderAvatar,
            $extMsgId, $body, $attachments, $sentAt
        ) {
            $timestamp = $sentAt ?? now();

            $conversation = Conversation::firstOrCreate(
                [
                    'social_profile_id' => $profile->id,
                    'external_conversation_id' => $extConvId,
                ],
                [
                    'workspace_id' => $profile->workspace_id,
                    'type' => $type,
                    'sender_name' => $senderName,
                    'sender_avatar' => $senderAvatar,
                    'sender_profile_id' => $senderId,
                    'status' => ConversationStatus::OPEN,
                    'is_unread' => true,
                    'last_activity_at' => $timestamp,
                ]
            );

            $existingMessage = Message::where('conversation_id', $conversation->id)
                ->where('external_message_id', $extMsgId)
                ->first();

            if (!$existingMessage) {
                $conversation->update([
                    'snippet' => Str::limit($body, 120),
                    'is_unread' => true,
                    'status' => ConversationStatus::OPEN,
                    'last_activity_at' => $timestamp,
                    'sender_name' => $senderName,
                    'sender_avatar' => $senderAvatar ?? $conversation->sender_avatar,
                ]);
            }

            return Message::updateOrCreate(
                [
                    'conversation_id' => $conversation->id,
                    'external_message_id' => $extMsgId,
                ],
                [
                    'direction' => MessageDirection::INBOUND,
                    'sender_name' => $senderName,
                    'sender_id' => $senderId,
                    'body' => $body,
                    'attachments' => $attachments,
                    'sent_at' => $timestamp,
                ]
            );
        });
    }

    /**
     * Send an outbound reply to the conversation.
     */
    public function sendReply(Conversation $conversation, User $staffUser, string $replyText): Message
    {
        return DB::transaction(function () use ($conversation, $staffUser, $replyText) {
            $profile = $conversation->socialProfile;
            $accessToken = $profile->access_token ?? $profile->connection?->access_token;

            // Live Platform API Reply dispatch
            if ($accessToken && !empty($conversation->external_conversation_id) && !str_starts_with($conversation->external_conversation_id, 'demo_')) {
                try {
                    $platform = $profile->platform instanceof \App\Enums\SocialPlatform
                        ? $profile->platform->value
                        : (string) $profile->platform;

                    if ($platform === 'youtube') {
                        Http::withToken($accessToken)->timeout(10)
                            ->post('https://www.googleapis.com/youtube/v3/comments?part=snippet', [
                                'snippet' => [
                                    'parentId' => $conversation->external_conversation_id,
                                    'textOriginal' => $replyText,
                                ],
                            ]);
                    } elseif ($platform === 'instagram') {
                        Http::withToken($accessToken)->timeout(10)
                            ->post("https://graph.facebook.com/v20.0/{$conversation->external_conversation_id}/replies", [
                                'message' => $replyText,
                            ]);
                    } elseif ($conversation->type === ConversationType::DIRECT_MESSAGE && !empty($conversation->sender_profile_id)) {
                        Http::withToken($accessToken)->timeout(10)
                            ->post("https://graph.facebook.com/v20.0/{$profile->profile_id}/messages", [
                                'recipient' => ['id' => $conversation->sender_profile_id],
                                'message' => ['text' => $replyText],
                            ]);
                    } else {
                        Http::withToken($accessToken)->timeout(10)
                            ->post("https://graph.facebook.com/v20.0/{$conversation->external_conversation_id}/comments", [
                                'message' => $replyText,
                            ]);
                    }
                } catch (\Exception $e) {
                    Log::warning("Provider reply dispatch skipped: " . $e->getMessage());
                }
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'external_message_id' => 'outbound_' . (string) Str::uuid(),
                'direction' => MessageDirection::OUTBOUND,
                'sender_name' => $staffUser->name,
                'sender_id' => (string) $staffUser->id,
                'body' => $replyText,
                'sent_by_user_id' => $staffUser->id,
                'sent_at' => now(),
            ]);

            $conversation->update([
                'snippet' => 'You: ' . Str::limit($replyText, 90),
                'last_activity_at' => now(),
            ]);

            return $message;
        });
    }

    /**
     * Mark a conversation as resolved.
     */
    public function resolveConversation(Conversation $conversation, User $user): void
    {
        $conversation->update([
            'status' => ConversationStatus::RESOLVED,
            'is_unread' => false,
        ]);
    }

    /**
     * Reopen an existing conversation.
     */
    public function reopenConversation(Conversation $conversation): void
    {
        $conversation->update([
            'status' => ConversationStatus::OPEN,
        ]);
    }

    /**
     * Assign a thread to a designated team member.
     */
    public function assignConversation(Conversation $conversation, User $assignTo, User $assignedBy): ConversationAssignment
    {
        return ConversationAssignment::create([
            'conversation_id' => $conversation->id,
            'assigned_to_user_id' => $assignTo->id,
            'assigned_by_user_id' => $assignedBy->id,
        ]);
    }

    /**
     * Poll Facebook, Instagram, and YouTube APIs for real comments and DMs.
     */
    public function syncLiveInteractions(\App\Models\Workspace $workspace, array &$syncErrors = []): int
    {
        // Clean up any old dummy demo threads in this workspace
        Conversation::where('workspace_id', $workspace->id)
            ->where('external_conversation_id', 'like', 'demo_%')
            ->delete();

        $profiles = SocialProfile::where('workspace_id', $workspace->id)
            ->where('is_active', true)
            ->whereIn('platform', [
                \App\Enums\SocialPlatform::FACEBOOK,
                \App\Enums\SocialPlatform::INSTAGRAM,
                \App\Enums\SocialPlatform::YOUTUBE,
            ])
            ->get();

        $ingested = 0;
        foreach ($profiles as $profile) {
            $token = $profile->access_token ?? $profile->connection?->access_token;
            if (!$token || !$profile->profile_id) {
                continue;
            }

            $platform = $profile->platform instanceof \App\Enums\SocialPlatform
                ? $profile->platform->value
                : (string) $profile->platform;

            try {
                if ($platform === 'facebook') {
                    // 1. Facebook Page Post Comments
                    $res = Http::withToken($token)->timeout(12)
                        ->get("https://graph.facebook.com/v20.0/{$profile->profile_id}/feed", [
                            'fields' => 'id,message,comments{id,message,from,created_time}',
                            'limit' => 10,
                        ]);

                    if ($res->successful()) {
                        foreach ($res->json('data') ?? [] as $post) {
                            foreach ($post['comments']['data'] ?? [] as $comment) {
                                $msg = $this->ingestInboundMessage(
                                    profile: $profile,
                                    extConvId: $comment['id'],
                                    type: ConversationType::COMMENT_THREAD,
                                    senderName: $comment['from']['name'] ?? 'Facebook Visitor',
                                    senderId: (string) ($comment['from']['id'] ?? 'fb_guest'),
                                    senderAvatar: null,
                                    extMsgId: $comment['id'],
                                    body: $comment['message'] ?? 'New comment',
                                    sentAt: isset($comment['created_time']) ? Carbon::parse($comment['created_time']) : now()
                                );
                                if ($msg->wasRecentlyCreated) $ingested++;
                            }
                        }
                    } elseif ($err = $res->json('error.message')) {
                        $syncErrors[] = "{$profile->name} (Comments): {$err}";
                    }

                    // 2. Facebook Page Messenger DMs
                    $dmRes = Http::withToken($token)->timeout(12)
                        ->get("https://graph.facebook.com/v20.0/{$profile->profile_id}/conversations", [
                            'fields' => 'id,updated_time,participants,messages.limit(10){id,message,from,created_time}',
                            'limit' => 10,
                        ]);

                    if ($dmRes->successful()) {
                        foreach ($dmRes->json('data') ?? [] as $thread) {
                            $participants = collect($thread['participants']['data'] ?? [])
                                ->first(fn ($p) => ($p['id'] ?? '') !== $profile->profile_id);
                            $senderName = $participants['name'] ?? 'Messenger User';
                            $senderId = (string) ($participants['id'] ?? 'fb_dm_user');

                            foreach (array_reverse($thread['messages']['data'] ?? []) as $m) {
                                if (($m['from']['id'] ?? '') === $profile->profile_id || empty($m['message'])) {
                                    continue;
                                }
                                $msg = $this->ingestInboundMessage(
                                    profile: $profile,
                                    extConvId: $thread['id'],
                                    type: ConversationType::DIRECT_MESSAGE,
                                    senderName: $m['from']['name'] ?? $senderName,
                                    senderId: (string) ($m['from']['id'] ?? $senderId),
                                    senderAvatar: null,
                                    extMsgId: $m['id'],
                                    body: $m['message'],
                                    sentAt: isset($m['created_time']) ? Carbon::parse($m['created_time']) : now()
                                );
                                if ($msg->wasRecentlyCreated) $ingested++;
                            }
                        }
                    }
                } elseif ($platform === 'instagram') {
                    // Instagram Media Comments
                    $igRes = Http::withToken($token)->timeout(12)
                        ->get("https://graph.facebook.com/v20.0/{$profile->profile_id}/media", [
                            'fields' => 'id,caption,comments{id,text,username,from,timestamp}',
                            'limit' => 10,
                        ]);

                    if ($igRes->successful()) {
                        foreach ($igRes->json('data') ?? [] as $media) {
                            foreach ($media['comments']['data'] ?? [] as $comment) {
                                $msg = $this->ingestInboundMessage(
                                    profile: $profile,
                                    extConvId: $comment['id'],
                                    type: ConversationType::COMMENT_THREAD,
                                    senderName: $comment['username'] ?? ($comment['from']['username'] ?? 'Instagram User'),
                                    senderId: (string) ($comment['from']['id'] ?? 'ig_guest'),
                                    senderAvatar: null,
                                    extMsgId: $comment['id'],
                                    body: $comment['text'] ?? 'New Instagram comment',
                                    sentAt: isset($comment['timestamp']) ? Carbon::parse($comment['timestamp']) : now()
                                );
                                if ($msg->wasRecentlyCreated) $ingested++;
                            }
                        }
                    } elseif ($err = $igRes->json('error.message')) {
                        $syncErrors[] = "{$profile->name} (Instagram): {$err}";
                    }
                } elseif ($platform === 'youtube') {
                    // YouTube Channel Comment Threads
                    $ytRes = Http::withToken($token)->timeout(12)
                        ->get('https://www.googleapis.com/youtube/v3/commentThreads', [
                            'part' => 'snippet',
                            'allThreadsRelatedToChannelId' => $profile->profile_id,
                            'maxResults' => 20,
                        ]);

                    if ($ytRes->successful()) {
                        foreach ($ytRes->json('items') ?? [] as $item) {
                            $top = $item['snippet']['topLevelComment']['snippet'] ?? [];
                            $commentId = $item['snippet']['topLevelComment']['id'] ?? $item['id'];
                            if (empty($top['textOriginal'] ?? $top['textDisplay'] ?? '')) {
                                continue;
                            }
                            $msg = $this->ingestInboundMessage(
                                profile: $profile,
                                extConvId: $commentId,
                                type: ConversationType::COMMENT_THREAD,
                                senderName: $top['authorDisplayName'] ?? 'YouTube Viewer',
                                senderId: (string) ($top['authorChannelId']['value'] ?? 'yt_guest'),
                                senderAvatar: $top['authorProfileImageUrl'] ?? null,
                                extMsgId: $commentId,
                                body: $top['textOriginal'] ?? strip_tags($top['textDisplay']),
                                sentAt: isset($top['publishedAt']) ? Carbon::parse($top['publishedAt']) : now()
                            );
                            if ($msg->wasRecentlyCreated) $ingested++;
                        }
                    } elseif ($err = $ytRes->json('error.message')) {
                        $syncErrors[] = "{$profile->name} (YouTube): {$err}";
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Live inbox sync failed for profile {$profile->id}: " . $e->getMessage());
                $syncErrors[] = "{$profile->name}: " . $e->getMessage();
            }
        }

        return $ingested;
    }

    /**
     * Generate a realistic sample conversation for testing the unified inbox.
     */
    public function generateSampleConversation(\App\Models\Workspace $workspace): Conversation
    {
        $profile = SocialProfile::where('workspace_id', $workspace->id)->first();
        if (!$profile) {
            $profile = SocialProfile::create([
                'workspace_id' => $workspace->id,
                'platform' => \App\Enums\SocialPlatform::FACEBOOK,
                'profile_id' => 'demo_page_' . Str::random(8),
                'name' => $workspace->name . ' Page',
                'is_active' => true,
                'publishing_enabled' => true,
            ]);
        }

        $sampleNames = ['Rahul Sharma', 'Ananya Verma', 'Vikram Malhotra', 'Priya Patel', 'Alex Morgan'];
        $sampleMessages = [
            'Hello! What are your business operating hours for this weekend?',
            'Loved your latest post! Can you share more details about your pricing plans?',
            'Do you offer customer support on WhatsApp as well?',
            'Great content! Looking forward to collaborating with your agency.',
        ];

        $randomName = $sampleNames[array_rand($sampleNames)];
        $randomMsg = $sampleMessages[array_rand($sampleMessages)];
        $uniqueId = 'demo_conv_' . Str::random(10);

        $msg = $this->ingestInboundMessage(
            profile: $profile,
            extConvId: $uniqueId,
            type: ConversationType::COMMENT_THREAD,
            senderName: $randomName,
            senderId: 'demo_user_' . rand(100, 999),
            senderAvatar: null,
            extMsgId: 'msg_' . Str::random(10),
            body: $randomMsg,
            sentAt: now()
        );

        return $msg->conversation;
    }
}

