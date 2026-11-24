<div class="approval-center-wrapper">
    <!-- Top Action & Filter Row -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-shield-check text-primary me-2"></i> Approval Center
            </h4>
            <p class="text-muted small mb-0">Multi-tier content review: Agency internal manager checks and client final sign-offs.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('composer') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-pencil-square me-1"></i> Compose Post
            </a>
        </div>
    </div>

    <!-- Flash message -->
    @if($feedbackMessage)
        <div class="alert alert-{{ $feedbackType }} alert-dismissible fade show mb-4 py-2 d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <div>{{ $feedbackMessage }}</div>
            <button type="button" class="btn-close py-2" wire:click="$set('feedbackMessage', null)"></button>
        </div>
    @endif

    <!-- Status Tabs Nav & Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pb-0 pt-3">
            <ul class="nav nav-tabs card-header-tabs border-bottom-0 gap-1">
                <li class="nav-item">
                    <button wire:click="setStatusFilter('pending')" class="nav-link py-2 px-3 fw-medium {{ $statusFilter === 'pending' ? 'active text-primary fw-bold' : 'text-muted' }}">
                        <i class="bi bi-hourglass-split me-1 text-warning"></i> Pending Review
                        <span class="badge bg-warning-subtle text-warning ms-1 rounded-pill">{{ $metrics['pending'] }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="setStatusFilter('changes_requested')" class="nav-link py-2 px-3 fw-medium {{ $statusFilter === 'changes_requested' ? 'active text-dark fw-bold' : 'text-muted' }}">
                        <i class="bi bi-arrow-counterclockwise me-1 text-secondary"></i> Changes Requested
                        <span class="badge bg-secondary-subtle text-secondary ms-1 rounded-pill">{{ $metrics['changes_requested'] }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="setStatusFilter('approved')" class="nav-link py-2 px-3 fw-medium {{ $statusFilter === 'approved' ? 'active text-success fw-bold' : 'text-muted' }}">
                        <i class="bi bi-check-circle me-1 text-success"></i> Approved
                        <span class="badge bg-success-subtle text-success ms-1 rounded-pill">{{ $metrics['approved'] }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="setStatusFilter('rejected')" class="nav-link py-2 px-3 fw-medium {{ $statusFilter === 'rejected' ? 'active text-danger fw-bold' : 'text-muted' }}">
                        <i class="bi bi-x-circle me-1 text-danger"></i> Rejected
                        <span class="badge bg-danger-subtle text-danger ms-1 rounded-pill">{{ $metrics['rejected'] }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="setStatusFilter('all')" class="nav-link py-2 px-3 fw-medium {{ $statusFilter === 'all' ? 'active text-primary fw-bold' : 'text-muted' }}">
                        All Reviews
                        <span class="badge bg-light text-muted ms-1 rounded-pill">{{ $metrics['all'] }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <!-- Filter Controls -->
        <div class="card-body bg-light border-top py-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select wire:model.live="selectedClientId" class="form-select form-select-sm">
                        <option value="">All Clients</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="stageFilter" class="form-select form-select-sm">
                        <option value="">All Review Stages</option>
                        @foreach($stages as $stage)
                            <option value="{{ $stage->value }}">{{ $stage->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 text-end">
                    @if($selectedClientId || $stageFilter)
                        <button wire:click="$set('selectedClientId', null); $set('stageFilter', '');" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset Filters
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Approvals List / Cards -->
    <div class="row g-4">
        @forelse($approvals as $approval)
            @php
                $post = $approval->post;
                $firstVariant = $post->variants->first();
                $firstMedia = $firstVariant?->mediaAssets?->first();
            @endphp
            <div class="col-12">
                <div class="card border shadow-sm rounded-3 overflow-hidden">
                    <!-- Approval Header Bar -->
                    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $approval->status->badgeClass() }}">
                                {{ $approval->status->label() }}
                            </span>
                            <span class="badge {{ $approval->current_stage->badgeClass() }}">
                                <i class="bi bi-diagram-3 me-1"></i> {{ $approval->current_stage->label() }}
                            </span>
                            @if($post->client)
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-building text-primary me-1"></i> {{ $post->client->name }}
                                </span>
                            @endif
                        </div>

                        <div class="small text-muted d-flex align-items-center gap-3">
                            <span>
                                <i class="bi bi-person me-1"></i> Submitted by <strong>{{ $approval->requestedBy?->name ?? 'User' }}</strong>
                            </span>
                            <span>
                                <i class="bi bi-clock me-1"></i> {{ $approval->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    <!-- Approval Card Content -->
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Left: Post Content, Media & Target Profiles -->
                            <div class="col-lg-7 border-end-lg">
                                <h6 class="fw-bold text-dark mb-2">
                                    {{ $post->title ?: 'Post Preview' }}
                                    @if($post->revision > 1)
                                        <span class="badge bg-light text-muted border ms-2">Revision v{{ $post->revision }}</span>
                                    @endif
                                </h6>

                                <div class="p-3 bg-light rounded text-dark fs-6 mb-3" style="white-space: pre-wrap; max-height: 180px; overflow-y: auto;">
                                    {{ $post->base_caption ?: 'No caption provided.' }}
                                </div>

                                @if($post->destination_link)
                                    <div class="mb-3 small">
                                        <a href="{{ $post->destination_link }}" target="_blank" class="text-primary text-decoration-none">
                                            <i class="bi bi-link-45deg me-1"></i>{{ $post->destination_link }}
                                        </a>
                                    </div>
                                @endif

                                <!-- Attached Media Thumbnails -->
                                @if($firstVariant && $firstVariant->mediaAssets->isNotEmpty())
                                    <div class="mb-3">
                                        <div class="small text-muted fw-medium mb-2">Attached Assets ({{ $firstVariant->mediaAssets->count() }}):</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($firstVariant->mediaAssets as $media)
                                                <img src="{{ $media->public_url }}" alt="Media" class="rounded border object-fit-cover shadow-xs" style="width: 64px; height: 64px;">
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Target Social Profiles -->
                                <div class="mb-3">
                                    <div class="small text-muted fw-medium mb-1">Target Social Profiles:</div>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($post->variants as $variant)
                                            @php $vPlat = $variant->socialProfile?->platform; @endphp
                                            <span class="badge bg-light text-dark border p-1 px-2 d-inline-flex align-items-center gap-1">
                                                <i class="bi {{ $vPlat?->icon() }} {{ $vPlat?->cssClass() }}"></i>
                                                {{ $variant->socialProfile?->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Scheduled Dispatch Time Notice -->
                                @if($post->scheduled_at)
                                    <div class="p-2 px-3 bg-indigo-subtle text-indigo rounded border border-indigo-subtle small d-flex align-items-center gap-2">
                                        <i class="bi bi-clock-history fs-5"></i>
                                        <div>
                                            Scheduled for <strong>{{ $post->scheduled_at->format('M d, Y h:i A') }}</strong>. 
                                            Upon final approval, worker queues will automatically publish this post on time.
                                        </div>
                                    </div>
                                @endif

                                <!-- Changes Requested / Rejection Callout -->
                                @if($approval->status === \App\Enums\ApprovalStatus::CHANGES_REQUESTED && $approval->rejection_reason)
                                    <div class="alert alert-warning py-2 px-3 mt-3 mb-0 small">
                                        <strong><i class="bi bi-exclamation-circle-fill me-1"></i> Changes Requested:</strong>
                                        {{ $approval->rejection_reason }}
                                    </div>
                                @elseif($approval->status === \App\Enums\ApprovalStatus::REJECTED && $approval->rejection_reason)
                                    <div class="alert alert-danger py-2 px-3 mt-3 mb-0 small">
                                        <strong><i class="bi bi-x-circle-fill me-1"></i> Rejection Reason:</strong>
                                        {{ $approval->rejection_reason }}
                                    </div>
                                @endif
                            </div>

                            <!-- Right: Audit Trail & Discussion Thread -->
                            <div class="col-lg-5 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-dark mb-2">Review Thread & Audit Trail</h6>

                                    <!-- Actions Timeline -->
                                    <div class="mb-3 p-2 bg-light rounded" style="max-height: 120px; overflow-y: auto;">
                                        @foreach($approval->actions as $action)
                                            <div class="small text-muted py-1 border-bottom border-light d-flex align-items-start gap-2">
                                                <i class="bi {{ $action->action->icon() }} mt-1"></i>
                                                <div class="flex-grow-1">
                                                    <div>
                                                        <strong>{{ $action->user?->name ?? 'User' }}</strong>: {{ $action->action->label() }}
                                                    </div>
                                                    @if($action->note)
                                                        <div class="fst-italic text-dark" style="font-size: 0.75rem;">"{{ $action->note }}"</div>
                                                    @endif
                                                </div>
                                                <span style="font-size: 0.7rem;">{{ $action->created_at->format('H:i') }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Comments Thread -->
                                    <div class="mb-3" style="max-height: 180px; overflow-y: auto;">
                                        @forelse($approval->comments as $comment)
                                            <div class="p-2 rounded mb-2 {{ $comment->is_internal ? 'bg-warning-subtle border border-warning-subtle' : 'bg-light border' }}">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fw-semibold text-dark small">
                                                        {{ $comment->user?->name }}
                                                        @if($comment->is_internal)
                                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">Internal Note</span>
                                                        @endif
                                                    </span>
                                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $comment->created_at->diffForHumans() }}</small>
                                                </div>
                                                <div class="small text-dark text-break">{{ $comment->message }}</div>
                                            </div>
                                        @empty
                                            <div class="text-center py-2 text-muted small fst-italic">No comments yet.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Comment Input Form -->
                                <div>
                                    <div class="input-group input-group-sm mb-1">
                                        <input type="text" wire:model="newComments.{{ $approval->id }}" wire:keydown.enter="postComment({{ $approval->id }})" class="form-control" placeholder="Type a comment or question...">
                                        <button wire:click="postComment({{ $approval->id }})" class="btn btn-outline-primary" type="button">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="internalCheck{{ $approval->id }}" wire:model="isInternalFlags.{{ $approval->id }}">
                                        <label class="form-check-label text-muted" for="internalCheck{{ $approval->id }}" style="font-size: 0.75rem;">
                                            <i class="bi bi-lock me-1"></i> Internal Agency Note (Hidden from client)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Card Footer Action Buttons -->
                    @if($approval->isPending())
                        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-end align-items-center gap-2">
                            <button wire:click="openRejectModal({{ $approval->id }})" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-x-circle me-1"></i> Reject Post
                            </button>

                            <button wire:click="openChangesModal({{ $approval->id }})" class="btn btn-sm btn-outline-warning text-dark">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Request Changes
                            </button>

                            <button wire:click="approve({{ $approval->id }})" class="btn btn-sm btn-success px-3">
                                <i class="bi bi-check2-circle me-1"></i> Approve Post
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-check2-all fs-1 text-success d-block mb-2"></i>
                <h5 class="fw-semibold text-dark">All Caught Up!</h5>
                <p class="text-muted small mb-0">No posts currently awaiting approval in this stage or status filter.</p>
            </div>
        @endforelse
    </div>

    @if($approvals->hasPages())
        <div class="mt-4">
            {{ $approvals->links() }}
        </div>
    @endif

    <!-- Request Changes Modal -->
    @if($showChangesModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header border-bottom py-3">
                        <h6 class="modal-title fw-bold text-dark mb-0">
                            <i class="bi bi-arrow-counterclockwise text-warning me-2"></i> Request Changes
                        </h6>
                        <button type="button" class="btn-close" wire:click="closeChangesModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="small text-muted mb-3">
                            Describe the requested edits or feedback. The post will be placed back into <strong>Draft</strong> mode so the creator can revise and resubmit it.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Feedback / Requested Edits <span class="text-danger">*</span></label>
                            <textarea wire:model="actionFeedbackNote" class="form-control" rows="4" placeholder="e.g. Please update the promotional discount to 25% and use high-res image..."></textarea>
                            @error('actionFeedbackNote')
                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="closeChangesModal">Cancel</button>
                        <button type="button" class="btn btn-sm btn-warning text-dark" wire:click="submitRequestChanges">
                            <i class="bi bi-send me-1"></i> Submit Feedback & Return to Draft
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Reject Modal -->
    @if($showRejectModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header border-bottom py-3">
                        <h6 class="modal-title fw-bold text-danger mb-0">
                            <i class="bi bi-x-circle text-danger me-2"></i> Reject Post
                        </h6>
                        <button type="button" class="btn-close" wire:click="closeRejectModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="small text-muted mb-3">
                            Rejecting this post will permanently set its status to <strong>Cancelled</strong>. Please provide a reason for the audit trail.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea wire:model="actionFeedbackNote" class="form-control" rows="4" placeholder="e.g. Campaign cancelled by client headquarters..."></textarea>
                            @error('actionFeedbackNote')
                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="closeRejectModal">Cancel</button>
                        <button type="button" class="btn btn-sm btn-danger" wire:click="submitReject">
                            <i class="bi bi-x-lg me-1"></i> Confirm Rejection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

