<?php

namespace App\Services\Approvals;

use App\Enums\ApprovalActionType;
use App\Enums\ApprovalStage;
use App\Enums\ApprovalStatus;
use App\Enums\PostStatus;
use App\Enums\WorkspaceRole;
use App\Models\Approval;
use App\Models\ApprovalAction;
use App\Models\ApprovalComment;
use App\Models\Post;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    /**
     * Submit a post into the multi-tier approval workflow.
     */
    public function submitForApproval(
        Post $post,
        User $submitter,
        ?int $assignedTo = null,
        ApprovalStage $stage = ApprovalStage::MANAGER_REVIEW,
        ?string $note = null
    ): Approval {
        return DB::transaction(function () use ($post, $submitter, $assignedTo, $stage, $note) {
            // Update parent post & all variants to pending_approval
            $post->update([
                'status' => PostStatus::PENDING_APPROVAL,
                'requires_approval' => true,
            ]);

            $post->variants()->update([
                'status' => PostStatus::PENDING_APPROVAL,
            ]);

            $approval = Approval::updateOrCreate(
                ['post_id' => $post->id],
                [
                    'workspace_id' => $post->workspace_id,
                    'status' => ApprovalStatus::PENDING,
                    'current_stage' => $stage,
                    'requested_by' => $submitter->id,
                    'assigned_to' => $assignedTo,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'rejection_reason' => null,
                ]
            );

            ApprovalAction::create([
                'approval_id' => $approval->id,
                'user_id' => $submitter->id,
                'action' => ApprovalActionType::SUBMITTED,
                'note' => $note ?? 'Submitted post for editorial review.',
                'created_at' => now(),
            ]);

            return $approval;
        });
    }

    /**
     * Approve a pending post. Advances stage if two-tier review is configured, or completes approval.
     */
    public function approve(Approval $approval, User $reviewer, ?string $note = null, bool $forceFinal = false): Approval
    {
        return DB::transaction(function () use ($approval, $reviewer, $note, $forceFinal) {
            $post = $approval->post;

            // Multi-Tier Check: If in manager_review and client has client reviewers, advance to client_review
            $clientHasReviewers = $post->client && $post->client->members()
                ->where('can_approve', true)
                ->exists();

            if ($approval->current_stage === ApprovalStage::MANAGER_REVIEW && $clientHasReviewers && !$forceFinal) {
                $approval->update([
                    'current_stage' => ApprovalStage::CLIENT_REVIEW,
                ]);

                ApprovalAction::create([
                    'approval_id' => $approval->id,
                    'user_id' => $reviewer->id,
                    'action' => ApprovalActionType::APPROVED,
                    'note' => $note ?? 'Manager review approved. Forwarded to client for final sign-off.',
                    'created_at' => now(),
                ]);

                return $approval;
            }

            // Final Approval achieved!
            $approval->update([
                'status' => ApprovalStatus::APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $nextStatus = $post->scheduled_at ? PostStatus::SCHEDULED : PostStatus::APPROVED;

            $post->update([
                'status' => $nextStatus,
            ]);

            $post->variants()->update([
                'status' => $nextStatus,
            ]);

            ApprovalAction::create([
                'approval_id' => $approval->id,
                'user_id' => $reviewer->id,
                'action' => ApprovalActionType::APPROVED,
                'note' => $note ?? 'Final approval granted. Post is ready for scheduled publishing.',
                'created_at' => now(),
            ]);

            return $approval;
        });
    }

    /**
     * Request changes on a post (sends back to Draft for revisions).
     */
    public function requestChanges(Approval $approval, User $reviewer, string $reason): Approval
    {
        return DB::transaction(function () use ($approval, $reviewer, $reason) {
            $approval->update([
                'status' => ApprovalStatus::CHANGES_REQUESTED,
                'rejection_reason' => $reason,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $post = $approval->post;
            $post->update([
                'status' => PostStatus::DRAFT,
                'revision' => $post->revision + 1,
            ]);

            $post->variants()->update([
                'status' => PostStatus::DRAFT,
            ]);

            ApprovalAction::create([
                'approval_id' => $approval->id,
                'user_id' => $reviewer->id,
                'action' => ApprovalActionType::REQUESTED_CHANGES,
                'note' => $reason,
                'created_at' => now(),
            ]);

            return $approval;
        });
    }

    /**
     * Reject and cancel a post.
     */
    public function reject(Approval $approval, User $reviewer, string $reason): Approval
    {
        return DB::transaction(function () use ($approval, $reviewer, $reason) {
            $approval->update([
                'status' => ApprovalStatus::REJECTED,
                'rejection_reason' => $reason,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $post = $approval->post;
            $post->update([
                'status' => PostStatus::CANCELLED,
            ]);

            $post->variants()->update([
                'status' => PostStatus::CANCELLED,
            ]);

            ApprovalAction::create([
                'approval_id' => $approval->id,
                'user_id' => $reviewer->id,
                'action' => ApprovalActionType::REJECTED,
                'note' => $reason,
                'created_at' => now(),
            ]);

            return $approval;
        });
    }

    /**
     * Post a comment or internal agency note to the approval thread.
     */
    public function addComment(Approval $approval, User $author, string $message, bool $isInternal = false): ApprovalComment
    {
        return ApprovalComment::create([
            'approval_id' => $approval->id,
            'user_id' => $author->id,
            'is_internal' => $isInternal,
            'message' => trim($message),
        ]);
    }
}
