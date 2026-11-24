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

            // Update snippet, reopen thread if closed, and bump last activity
            $conversation->update([
                'snippet' => Str::limit($body, 120),
                'is_unread' => true,
                'status' => ConversationStatus::OPEN,
                'last_activity_at' => $timestamp,
                'sender_name' => $senderName,
                'sender_avatar' => $senderAvatar ?? $conversation->sender_avatar,
            ]);

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

            // Mock/Live Meta Graph API Comment Reply dispatch
            if ($accessToken && config('services.facebook.live_replies', false)) {
                try {
                    $url = "https://graph.facebook.com/v20.0/{$conversation->external_conversation_id}/comments";
                    Http::withToken($accessToken)->post($url, ['message' => $replyText]);
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
}

