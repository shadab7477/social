<div class="automations-wrapper">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-robot me-2 text-primary"></i> Automations &amp; RSS
            </h4>
            <p class="text-muted small mb-0">Automate RSS feed posting, recurring queues, analytics sync, and scheduled reports.</p>
        </div>
        <button type="button" wire:click="$set('showCreateModal', true)" class="btn-black-pill">
            <i class="bi bi-plus-lg"></i> <span>New Automation</span>
        </button>
    </div>

    @if ($feedbackMessage)
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 py-2 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>{{ $feedbackMessage }}</div>
            <button type="button" class="btn-close py-2" wire:click="$set('feedbackMessage', null)"></button>
        </div>
    @endif

    <!-- Stats Row + Activity Chart -->
    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="row g-3 h-100">
                <div class="col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                        <div class="small text-muted mb-1">Active Workflows</div>
                        <div class="fs-3 fw-bold text-dark">{{ $stats['active_rules'] + 2 }}</div>
                        <small class="text-success"><i class="bi bi-lightning-charge-fill"></i> Running</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                        <div class="small text-muted mb-1">Queued Posts</div>
                        <div class="fs-3 fw-bold text-primary">{{ $stats['scheduled'] }}</div>
                        <small class="text-muted">Auto-dispatched</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                        <div class="small text-muted mb-1">Published Total</div>
                        <div class="fs-3 fw-bold text-success">{{ $stats['published'] }}</div>
                        <small class="text-muted">All profiles</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                        <div class="small text-muted mb-1">Report Schedules</div>
                        <div class="fs-3 fw-bold text-dark">{{ $stats['report_schedules'] }}</div>
                        <small class="text-muted">Weekly &amp; Monthly</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">7-Day Publishing &amp; Schedule Activity</h6>
                </div>
                <div class="card-body p-3" wire:ignore>
                    <div class="position-relative w-100" style="height: 200px;">
                        <canvas id="automationActivityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Built-in System Automations + Custom RSS Rules -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h6 class="fw-bold mb-0 text-dark">Configured Workflows</h6>
        </div>
        <div class="list-group list-group-flush">
            <!-- Built-in Scheduler -->
            <div class="list-group-item py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-clock-history fs-5"></i>
                    </span>
                    <div>
                        <div class="fw-bold text-dark small">Auto Post Scheduler (Every Minute)</div>
                        <div class="text-muted" style="font-size: 0.78rem;">Automatically publishes scheduled posts when their dispatch time arrives.</div>
                    </div>
                </div>
                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">System Active</span>
            </div>

            <!-- Built-in Analytics Sync -->
            <div class="list-group-item py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-graph-up-arrow fs-5"></i>
                    </span>
                    <div>
                        <div class="fw-bold text-dark small">Analytics Sync Engine (Every 2 Hours)</div>
                        <div class="text-muted" style="font-size: 0.78rem;">Refreshes reach, impressions, followers, and engagement across connected accounts.</div>
                    </div>
                </div>
                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">System Active</span>
            </div>

            @foreach ($automations as $rule)
                <div class="list-group-item py-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                        <span class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bi {{ ($rule['type'] ?? '') === 'rss_feed' ? 'bi-rss-fill' : 'bi-arrow-repeat' }} fs-5"></i>
                        </span>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold text-dark small text-truncate">{{ $rule['name'] }}</div>
                            <div class="text-muted text-break" style="font-size: 0.75rem;">
                                {{ ucwords(str_replace('_', ' ', $rule['type'])) }}
                                @if(!empty($rule['source_url'])) &bull; {{ $rule['source_url'] }} @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <button type="button" wire:click="toggleAutomation('{{ $rule['id'] }}')" class="btn btn-sm rounded-pill px-3 {{ ($rule['is_active'] ?? true) ? 'btn-success' : 'btn-outline-secondary' }}">
                            {{ ($rule['is_active'] ?? true) ? 'Enabled' : 'Paused' }}
                        </button>
                        <button type="button" wire:click="deleteAutomation('{{ $rule['id'] }}')" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete" aria-label="Delete workflow">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Create Automation Modal -->
    @if ($showCreateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15,23,42,0.55); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom py-3">
                        <h6 class="modal-title fw-bold mb-0">Create Automation Workflow</h6>
                        <button type="button" class="btn-close" wire:click="$set('showCreateModal', false)"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Workflow Name</label>
                            <input type="text" class="form-control" wire:model="newName" placeholder="e.g. Company Blog RSS Auto-Share">
                            @error('newName') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Automation Type</label>
                            <select class="form-select" wire:model="newType">
                                <option value="rss_feed">RSS Feed Auto-Post</option>
                                <option value="evergreen_queue">Evergreen Content Recycling</option>
                                <option value="auto_analytics">Scheduled Analytics Digest</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">RSS / Feed URL (Optional)</label>
                            <input type="url" class="form-control" wire:model="newSourceUrl" placeholder="https://example.com/feed.xml">
                            @error('newSourceUrl') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div>
                            <label class="form-label small fw-bold">Target Account</label>
                            <select class="form-select" wire:model="newTargetProfileId">
                                <option value="">All Connected Accounts</option>
                                @foreach ($profiles as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="$set('showCreateModal', false)">Cancel</button>
                        <button type="button" wire:click="createAutomation" class="btn btn-sm btn-dark rounded-pill px-4">Create Workflow</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('livewire:navigated', renderAutomationChart);
document.addEventListener('DOMContentLoaded', renderAutomationChart);

function renderAutomationChart() {
    const ctx = document.getElementById('automationActivityChart');
    if (!ctx || !window.Chart) return;
    if (ctx._chartInstance) ctx._chartInstance.destroy();
    ctx._chartInstance = new window.Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartData['labels']),
            datasets: [
                { label: 'Published', data: @json($chartData['published']), backgroundColor: '#0f172a', borderRadius: 6 },
                { label: 'Scheduled', data: @json($chartData['scheduled']), backgroundColor: '#3b82f6', borderRadius: 6 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
}
</script>
@endpush

