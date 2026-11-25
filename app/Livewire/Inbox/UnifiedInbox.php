<?php

namespace App\Livewire\Inbox;

use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Enums\SocialPlatform;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\SocialProfile;
use App\Models\User;
use App\Services\Inbox\SocialInboxService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class UnifiedInbox extends Component
{
    // Folder & Filter Navigation
    public string $folder = 'open'; // 'open', 'unread', 'assigned_me', 'resolved', 'spam'
    public ?int $selectedClientId = null;
    public string $selectedPlatform = '';
    public string $search = '';

    // Active Conversation
    public ?int $selectedConversationId = null;
    public string $replyText = '';
    public ?int $assignToUserId = null;

    // Feedback Alert
    public ?string $feedbackMessage = null;
    public string $feedbackType = 'success';

    public function mount(): void
    {
        // Select first conversation if available
        $first = Conversation::where('status', ConversationStatus::OPEN)
            ->latest('last_activity_at')
            ->first();

        if ($first) {
            $this->selectedConversationId = $first->id;
            $first->update(['is_unread' => false]);
        }
    }

    public function setFolder(string $folderName): void
    {
        $this->folder = $folderName;
    }

    public function selectConversation(int $id): void
    {
        $this->selectedConversationId = $id;
        $this->replyText = '';

        $conv = Conversation::find($id);
        if ($conv && $conv->is_unread) {
            $conv->update(['is_unread' => false]);
        }
    }

    /**
     * Send outbound reply to active thread.
     */
    public function sendReply(?SocialInboxService $inboxService = null): void
    {
        $inboxService = $inboxService ?? app(SocialInboxService::class);
        $this->validate([
            'replyText' => 'required|min:1|max:2000',
        ]);

        if (!$this->selectedConversationId) {
            return;
        }

        $conv = Conversation::with('socialProfile.connection')->find($this->selectedConversationId);
        if (!$conv) {
            return;
        }

        $inboxService->sendReply($conv, Auth::user(), $this->replyText);

        $this->replyText = '';
        $this->feedbackMessage = "Reply dispatched to {$conv->sender_name}!";
        $this->feedbackType = 'success';
    }

    /**
     * Mark active thread as resolved.
     */
    public function markResolved(?SocialInboxService $inboxService = null): void
    {
        $inboxService = $inboxService ?? app(SocialInboxService::class);
        if (!$this->selectedConversationId) {
            return;
        }

        $conv = Conversation::find($this->selectedConversationId);
        if ($conv) {
            $inboxService->resolveConversation($conv, Auth::user());
            $this->feedbackMessage = "Conversation marked as resolved.";
            $this->feedbackType = 'success';
        }
    }

    /**
     * Reopen a resolved conversation.
     */
    public function reopen(?SocialInboxService $inboxService = null): void
    {
        $inboxService = $inboxService ?? app(SocialInboxService::class);
        if (!$this->selectedConversationId) {
            return;
        }

        $conv = Conversation::find($this->selectedConversationId);
        if ($conv) {
            $inboxService->reopenConversation($conv);
            $this->feedbackMessage = "Conversation reopened.";
            $this->feedbackType = 'info';
        }
    }

    /**
     * Assign active conversation to another team member.
     */
    public function assignToMember(?SocialInboxService $inboxService = null): void
    {
        $inboxService = $inboxService ?? app(SocialInboxService::class);
        if (!$this->selectedConversationId || !$this->assignToUserId) {
            return;
        }

        $conv = Conversation::find($this->selectedConversationId);
        $user = User::find($this->assignToUserId);

        if ($conv && $user) {
            $inboxService->assignConversation($conv, $user, Auth::user());
            $this->feedbackMessage = "Thread assigned to {$user->name}.";
            $this->feedbackType = 'info';
        }
    }

    /**
     * Apply quick canned response template to reply text.
     */
    public function applyTemplate(string $text): void
    {
        $this->replyText = $text;
    }

    /**
     * Synchronize live comments from connected social accounts.
     */
    public function syncLiveComments(?SocialInboxService $inboxService = null): void
    {
        $inboxService = $inboxService ?? app(SocialInboxService::class);
        $workspace = app('current_workspace');
        $count = $inboxService->syncLiveInteractions($workspace);

        if ($count > 0) {
            $this->feedbackMessage = "Successfully synced {$count} live comment(s) from your connected social pages!";
            $this->feedbackType = 'success';
        } else {
            $this->feedbackMessage = "Checked connected social accounts. No new comments were found on your pages. (Tip: Use '⚡ Add Test Interaction' to test replying in real-time).";
            $this->feedbackType = 'info';
        }

        if (!$this->selectedConversationId) {
            $first = Conversation::where('status', ConversationStatus::OPEN)->latest('last_activity_at')->first();
            if ($first) {
                $this->selectedConversationId = $first->id;
            }
        }
    }

    /**
     * Generate a realistic test conversation so user can immediately test replying.
     */
    public function generateTestInteraction(?SocialInboxService $inboxService = null): void
    {
        $inboxService = $inboxService ?? app(SocialInboxService::class);
        $workspace = app('current_workspace');
        $conv = $inboxService->generateSampleConversation($workspace);
        $this->selectedConversationId = $conv->id;
        $this->feedbackMessage = "Test interaction generated from {$conv->sender_name}! You can now type a reply below.";
        $this->feedbackType = 'success';
    }

    public function render()
    {
        $query = Conversation::query()
            ->with(['socialProfile.client', 'currentAssignment.assignedToUser'])
            ->latest('last_activity_at');

        // Folder Filtering
        if ($this->folder === 'open') {
            $query->where('status', ConversationStatus::OPEN);
        } elseif ($this->folder === 'unread') {
            $query->where('is_unread', true);
        } elseif ($this->folder === 'assigned_me') {
            $query->whereHas('currentAssignment', fn($a) => $a->where('assigned_to_user_id', Auth::id()));
        } elseif ($this->folder === 'resolved') {
            $query->where('status', ConversationStatus::RESOLVED);
        } elseif ($this->folder === 'spam') {
            $query->whereIn('status', [ConversationStatus::SPAM, ConversationStatus::ARCHIVED]);
        }

        // Client Filtering
        if ($this->selectedClientId) {
            $query->whereHas('socialProfile', fn($p) => $p->where('client_id', $this->selectedClientId));
        }

        // Platform Filtering
        if ($this->selectedPlatform) {
            $query->whereHas('socialProfile', fn($p) => $p->where('platform', $this->selectedPlatform));
        }

        // Search
        if (!empty(trim($this->search))) {
            $search = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('sender_name', 'like', $search)
                  ->orWhere('snippet', 'like', $search);
            });
        }

        $conversations = $query->take(50)->get();

        $activeConversation = null;
        if ($this->selectedConversationId) {
            $activeConversation = Conversation::with([
                'socialProfile.client',
                'messages.sentByUser',
                'currentAssignment.assignedToUser',
            ])->find($this->selectedConversationId);
        }

        $clients = Client::orderBy('name')->get();
        $teamMembers = Auth::user()->activeWorkspace()?->members()->with('user')->get()->pluck('user') ?? collect();

        // Metrics for folder badges
        $metrics = [
            'open' => Conversation::where('status', ConversationStatus::OPEN)->count(),
            'unread' => Conversation::where('is_unread', true)->count(),
            'assigned_me' => Conversation::whereHas('currentAssignment', fn($a) => $a->where('assigned_to_user_id', Auth::id()))->count(),
            'resolved' => Conversation::where('status', ConversationStatus::RESOLVED)->count(),
        ];

        return view('livewire.inbox.unified-inbox', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'clients' => $clients,
            'platforms' => SocialPlatform::cases(),
            'teamMembers' => $teamMembers,
            'metrics' => $metrics,
        ]);
    }
}

