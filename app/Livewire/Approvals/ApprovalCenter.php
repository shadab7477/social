<?php

namespace App\Livewire\Approvals;

use App\Enums\ApprovalActionType;
use App\Enums\ApprovalStage;
use App\Enums\ApprovalStatus;
use App\Enums\WorkspaceRole;
use App\Models\Approval;
use App\Models\ApprovalComment;
use App\Models\Client;
use App\Services\Approvals\ApprovalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ApprovalCenter extends Component
{
    use WithPagination;

    // Filters
    public string $statusFilter = 'pending'; // pending, changes_requested, approved, rejected, all
    public string $stageFilter = ''; // manager_review, client_review, or empty
    public ?int $selectedClientId = null;

    // Modal & Action State
    public ?int $activeApprovalId = null;
    public string $actionFeedbackNote = '';
    public bool $showChangesModal = false;
    public bool $showRejectModal = false;

    // Comment Input State
    public array $newComments = []; // [approvalId => 'message']
    public array $isInternalFlags = []; // [approvalId => bool]

    // Flash Feedback
    public ?string $feedbackMessage = null;
    public string $feedbackType = 'success';

    protected $paginationTheme = 'bootstrap';

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function updatingStageFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedClientId(): void
    {
        $this->resetPage();
    }

    /**
     * 1-Click Approve Post.
     */
    public function approve(int $approvalId, ApprovalService $approvalService): void
    {
        $approval = Approval::with(['post.client.members'])->find($approvalId);
        if (!$approval) {
            return;
        }

        $user = Auth::user();
        $approvalService->approve($approval, $user);

        $this->feedbackMessage = "Post #{$approval->post_id} approval action processed successfully!";
        $this->feedbackType = 'success';
    }

    /**
     * Open Request Changes Modal.
     */
    public function openChangesModal(int $approvalId): void
    {
        $this->activeApprovalId = $approvalId;
        $this->actionFeedbackNote = '';
        $this->showChangesModal = true;
    }

    public function closeChangesModal(): void
    {
        $this->showChangesModal = false;
        $this->activeApprovalId = null;
        $this->actionFeedbackNote = '';
    }

    /**
     * Submit Request Changes.
     */
    public function submitRequestChanges(ApprovalService $approvalService): void
    {
        $this->validate([
            'actionFeedbackNote' => 'required|min:5|max:1000',
        ]);

        if (!$this->activeApprovalId) {
            return;
        }

        $approval = Approval::find($this->activeApprovalId);
        if ($approval) {
            $approvalService->requestChanges($approval, Auth::user(), $this->actionFeedbackNote);
            $this->feedbackMessage = "Changes requested on post #{$approval->post_id}. Post returned to draft.";
            $this->feedbackType = 'warning';
        }

        $this->closeChangesModal();
    }

    /**
     * Open Reject Modal.
     */
    public function openRejectModal(int $approvalId): void
    {
        $this->activeApprovalId = $approvalId;
        $this->actionFeedbackNote = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->activeApprovalId = null;
        $this->actionFeedbackNote = '';
    }

    /**
     * Submit Post Rejection.
     */
    public function submitReject(ApprovalService $approvalService): void
    {
        $this->validate([
            'actionFeedbackNote' => 'required|min:5|max:1000',
        ]);

        if (!$this->activeApprovalId) {
            return;
        }

        $approval = Approval::find($this->activeApprovalId);
        if ($approval) {
            $approvalService->reject($approval, Auth::user(), $this->actionFeedbackNote);
            $this->feedbackMessage = "Post #{$approval->post_id} has been rejected and cancelled.";
            $this->feedbackType = 'danger';
        }

        $this->closeRejectModal();
    }

    /**
     * Post comment to approval discussion thread.
     */
    public function postComment(int $approvalId, ApprovalService $approvalService): void
    {
        $message = trim($this->newComments[$approvalId] ?? '');
        if (empty($message)) {
            return;
        }

        $approval = Approval::find($approvalId);
        if (!$approval) {
            return;
        }

        $isInternal = !empty($this->isInternalFlags[$approvalId]);

        $approvalService->addComment($approval, Auth::user(), $message, $isInternal);

        $this->newComments[$approvalId] = '';
        $this->isInternalFlags[$approvalId] = false;

        $this->feedbackMessage = "Comment added to review thread.";
        $this->feedbackType = 'info';
    }

    public function render()
    {
        $query = Approval::query()
            ->with([
                'post.client',
                'post.variants.socialProfile',
                'post.variants.mediaAssets',
                'requestedBy',
                'reviewedBy',
                'actions.user',
                'comments.user',
            ])
            ->latest();

        // Status Filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Stage Filter
        if ($this->stageFilter) {
            $query->where('current_stage', $this->stageFilter);
        }

        // Client Filter
        if ($this->selectedClientId) {
            $query->whereHas('post', fn($q) => $q->where('client_id', $this->selectedClientId));
        }

        $approvals = $query->paginate(8);
        $clients = Client::orderBy('name')->get();

        // Metric Counts
        $metrics = [
            'pending' => Approval::where('status', ApprovalStatus::PENDING)->count(),
            'changes_requested' => Approval::where('status', ApprovalStatus::CHANGES_REQUESTED)->count(),
            'approved' => Approval::where('status', ApprovalStatus::APPROVED)->count(),
            'rejected' => Approval::where('status', ApprovalStatus::REJECTED)->count(),
            'all' => Approval::count(),
        ];

        return view('livewire.approvals.approval-center', [
            'approvals' => $approvals,
            'clients' => $clients,
            'metrics' => $metrics,
            'stages' => ApprovalStage::cases(),
        ]);
    }
}

