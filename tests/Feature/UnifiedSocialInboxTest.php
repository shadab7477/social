<?php

namespace Tests\Feature;

use App\Enums\ConnectionStatus;
use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Enums\MessageDirection;
use App\Enums\SocialPlatform;
use App\Enums\WorkspaceRole;
use App\Livewire\Inbox\UnifiedInbox;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\Inbox\SocialInboxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UnifiedSocialInboxTest extends TestCase
{
    use RefreshDatabase;

    protected User $agent;
    protected Workspace $workspace;
    protected Client $client;
    protected SocialProfile $profile;
    protected SocialInboxService $inboxService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inboxService = app(SocialInboxService::class);

        $this->agent = User::factory()->create(['name' => 'Community Manager']);
        $this->workspace = Workspace::create([
            'name' => 'Omni Social Group',
            'owner_id' => $this->agent->id,
            'plan_type' => 'pro',
            'max_profiles' => 20,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->agent->id,
            'role' => WorkspaceRole::OWNER,
            'is_active' => true,
        ]);

        $this->agent->update(['current_workspace_id' => $this->workspace->id]);
        $this->actingAs($this->agent);
        app()->instance('current_workspace', $this->workspace);

        $this->client = Client::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Sweet Treats Bakery',
            'client_code' => 'STB01',
            'status' => 'active',
        ]);

        $connection = SocialConnection::create([
            'workspace_id' => $this->workspace->id,
            'platform' => SocialPlatform::FACEBOOK,
            'account_id' => 'fb_user_444',
            'account_name' => 'Bakery Owner',
            'access_token' => 'mock_token',
            'scopes' => ['pages_messaging', 'pages_read_engagement', 'pages_manage_posts'],
            'status' => ConnectionStatus::CONNECTED,
        ]);

        $this->profile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $connection->id,
            'client_id' => $this->client->id,
            'platform' => SocialPlatform::FACEBOOK,
            'profile_id' => 'fb_page_444',
            'name' => 'Sweet Treats Official FB',
            'capabilities' => ['supports_comments' => true, 'supports_messages' => true],
            'publishing_enabled' => true,
            'is_active' => true,
        ]);
    }

    public function test_can_ingest_inbound_social_comment(): void
    {
        $message = $this->inboxService->ingestInboundMessage(
            $this->profile,
            'ext_thread_1001',
            ConversationType::COMMENT_THREAD,
            'Sarah Jenkins',
            'cust_user_99',
            'https://example.com/sarah.jpg',
            'ext_msg_2001',
            'Do you have gluten free cupcakes available today?'
        );

        $this->assertNotNull($message);
        $this->assertEquals(MessageDirection::INBOUND, $message->direction);
        $this->assertEquals('Do you have gluten free cupcakes available today?', $message->body);

        $conversation = $message->conversation;
        $this->assertEquals($this->profile->id, $conversation->social_profile_id);
        $this->assertEquals('Sarah Jenkins', $conversation->sender_name);
        $this->assertEquals(ConversationStatus::OPEN, $conversation->status);
        $this->assertTrue($conversation->is_unread);
    }

    public function test_can_send_outbound_reply_and_update_conversation(): void
    {
        $inboundMsg = $this->inboxService->ingestInboundMessage(
            $this->profile,
            'ext_thread_1002',
            ConversationType::COMMENT_THREAD,
            'Alex Rivera',
            'cust_user_88',
            null,
            'ext_msg_2002',
            'What are your weekend store hours?'
        );

        $conversation = $inboundMsg->conversation;

        $reply = $this->inboxService->sendReply(
            $conversation,
            $this->agent,
            'We are open Saturday and Sunday from 8am to 6pm!'
        );

        $this->assertEquals(MessageDirection::OUTBOUND, $reply->direction);
        $this->assertEquals($this->agent->id, $reply->sent_by_user_id);

        $conversation->refresh();
        $this->assertStringContainsString('You:', $conversation->snippet);
        $this->assertCount(2, $conversation->messages);
    }

    public function test_can_resolve_and_reopen_conversation(): void
    {
        $message = $this->inboxService->ingestInboundMessage(
            $this->profile,
            'ext_thread_1003',
            ConversationType::COMMENT_THREAD,
            'David Lee',
            'cust_user_77',
            null,
            'ext_msg_2003',
            'Loved the chocolate chip cookies!'
        );

        $conversation = $message->conversation;

        $this->inboxService->resolveConversation($conversation, $this->agent);
        $this->assertEquals(ConversationStatus::RESOLVED, $conversation->fresh()->status);
        $this->assertFalse($conversation->fresh()->is_unread);

        $this->inboxService->reopenConversation($conversation);
        $this->assertEquals(ConversationStatus::OPEN, $conversation->fresh()->status);
    }

    public function test_livewire_unified_inbox_component_workflow(): void
    {
        $msg = $this->inboxService->ingestInboundMessage(
            $this->profile,
            'ext_thread_1004',
            ConversationType::COMMENT_THREAD,
            'Emma Watson',
            'cust_user_66',
            null,
            'ext_msg_2004',
            'Can I place an order for custom birthday cakes?'
        );

        Livewire::test(UnifiedInbox::class)
            ->assertOk()
            ->assertSee('Social Inbox')
            ->assertSee('Emma Watson')
            ->assertSee('Can I place an order')
            ->set('replyText', 'Yes! Please email us at bakery@example.com for custom cake quotes.')
            ->call('sendReply')
            ->assertSee('Reply dispatched to Emma Watson!')
            ->call('markResolved')
            ->assertSee('marked as resolved');

        $this->assertDatabaseHas('messages', [
            'body' => 'Yes! Please email us at bakery@example.com for custom cake quotes.',
            'direction' => MessageDirection::OUTBOUND->value,
        ]);

        $this->assertEquals(ConversationStatus::RESOLVED, $msg->conversation->fresh()->status);
    }
}

