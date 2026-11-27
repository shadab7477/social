<div class="report-builder-wrapper">
    <!-- Top Action & Filter Row -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i> Client Reports
            </h4>
            <p class="text-muted small mb-0">Compile agency-branded executive summaries, performance PDFs, and CSV datasets for your clients.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button wire:click="openScheduleModal" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-clock-history me-1"></i> Recurring Schedule
            </button>
            <button wire:click="openCreateModal" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-lg me-1"></i> Generate Report
            </button>
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

    <!-- Navigation Tabs -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pb-0 pt-3">
            <ul class="nav nav-tabs nav-tabs-scrollable card-header-tabs border-bottom-0 gap-1">
                <li class="nav-item">
                    <button wire:click="$set('activeTab', 'reports')" class="nav-link py-2 px-3 fw-medium {{ $activeTab === 'reports' ? 'active text-primary fw-bold' : 'text-muted' }}">
                        <i class="bi bi-archive me-1"></i> Generated Reports Archive
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="$set('activeTab', 'schedules')" class="nav-link py-2 px-3 fw-medium {{ $activeTab === 'schedules' ? 'active text-primary fw-bold' : 'text-muted' }}">
                        <i class="bi bi-send-check me-1"></i> Automated Client Dispatches
                        <span class="badge bg-secondary-subtle text-secondary ms-1 rounded-pill">{{ $schedules->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>

        @if($activeTab === 'reports')
            <!-- Filter Controls for Reports -->
            <div class="card-body bg-light border-top py-2 px-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <select wire:model.live="selectedClientId" class="form-select form-select-sm">
                            <option value="">All Clients</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <select wire:model.live="formatFilter" class="form-select form-select-sm">
                            <option value="">All Formats (PDF & CSV)</option>
                            <option value="pdf">PDF Executive Summary</option>
                            <option value="csv">CSV Raw Data Export</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        @if($selectedClientId || $formatFilter)
                            <button wire:click="$set('selectedClientId', null); $set('formatFilter', '');" class="btn btn-sm btn-link text-muted p-0">
                                Reset
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($activeTab === 'reports')
        <!-- Reports Archive Table -->
        <div class="card border-0 shadow-sm">
            <div class="table-wrapper table-responsive" tabindex="0" role="region" aria-label="Generated reports archive">
                <table class="table table-hover align-middle mb-0 table-stack-mobile">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th style="width: 320px;">Report Title</th>
                            <th>Client</th>
                            <th>Format</th>
                            <th>Reporting Period</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th class="text-end" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody wire:loading.remove wire:target="selectedClientId, formatFilter, activeTab">
                        @forelse($reports as $report)
                            <tr>
                                <td class="td-primary-stack" data-label="Report">
                                    <div class="fw-semibold text-dark text-break">{{ $report->title }}</div>
                                    <small class="text-muted">{{ $report->created_at->format('M d, Y h:i A') }}</small>
                                </td>
                                <td data-label="Client">
                                    @if($report->client)
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-building text-primary me-1"></i> {{ $report->client->name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">Workspace Global</span>
                                    @endif
                                </td>
                                <td data-label="Format">
                                    <span class="badge {{ $report->format->badgeClass() }} d-inline-flex align-items-center gap-1">
                                        <i class="bi {{ $report->format->icon() }}"></i>
                                        {{ strtoupper($report->format->value) }}
                                    </span>
                                </td>
                                <td data-label="Period">
                                    <span class="small text-dark fw-medium">
                                        {{ $report->date_from->format('M d, Y') }} – {{ $report->date_to->format('M d, Y') }}
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span class="badge {{ $report->status->badgeClass() }}">
                                        {{ $report->status->label() }}
                                    </span>
                                </td>
                                <td data-label="Created By">
                                    <span class="small text-muted">{{ $report->createdBy?->name ?? 'System' }}</span>
                                </td>
                                <td class="text-end" data-label="Actions">
                                    <div class="d-flex justify-content-end gap-1">
                                        @if($report->isReady())
                                            <a href="{{ route('reports.download', $report->id) }}" target="_blank" download class="btn btn-sm btn-outline-primary" title="Download Report" aria-label="Download Report">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            @if($report->format === \App\Enums\ReportFormat::PDF && $report->file_url)
                                                <a href="{{ $report->file_url }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="View in Browser" aria-label="View in Browser">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            @endif
                                        @endif
                                        <button wire:click="deleteReport({{ $report->id }})" wire:confirm="Are you sure you want to delete this report?" class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete report">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-secondary"></i>
                                    <h6 class="fw-semibold">No reports generated yet</h6>
                                    <p class="small mb-3">Click the button below to generate your first client performance report.</p>
                                    <button wire:click="openCreateModal" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Generate First Report
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tbody wire:loading.delay wire:target="selectedClientId, formatFilter, activeTab">
                        @for($i = 0; $i < 4; $i++)
                            <tr>
                                <td>
                                    <div class="skeleton-box mb-1" style="width: 70%; height: 16px;"></div>
                                    <div class="skeleton-box" style="width: 40%; height: 12px;"></div>
                                </td>
                                <td><div class="skeleton-box skeleton-pill" style="width: 80px; height: 20px;"></div></td>
                                <td><div class="skeleton-box skeleton-pill" style="width: 60px; height: 20px;"></div></td>
                                <td><div class="skeleton-box" style="width: 110px; height: 14px;"></div></td>
                                <td><div class="skeleton-box skeleton-pill" style="width: 70px; height: 20px;"></div></td>
                                <td><div class="skeleton-box" style="width: 65px; height: 14px;"></div></td>
                                <td class="text-end"><div class="skeleton-box" style="width: 80px; height: 28px; border-radius: 6px;"></div></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            @if($reports->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>

    @else
        <!-- Automated Schedules Tab -->
        <div class="card border-0 shadow-sm">
            <div class="table-wrapper table-responsive" tabindex="0" role="region" aria-label="Automated dispatch schedules">
                <table class="table table-hover align-middle mb-0 table-stack-mobile">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th>Schedule Name</th>
                            <th>Target Client</th>
                            <th>Frequency</th>
                            <th>Recipients</th>
                            <th>Status</th>
                            <th>Last Dispatched</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody wire:loading.remove wire:target="selectedClientId, formatFilter, activeTab">
                        @forelse($schedules as $sched)
                            <tr>
                                <td class="fw-semibold text-dark td-primary-stack" data-label="Schedule">{{ $sched->name }}</td>
                                <td data-label="Client">
                                    @if($sched->client)
                                        <span class="badge bg-light text-dark border">{{ $sched->client->name }}</span>
                                    @else
                                        <span class="text-muted small">Entire Workspace</span>
                                    @endif
                                </td>
                                <td data-label="Frequency">
                                    <span class="badge bg-indigo-subtle text-indigo">
                                        {{ ucfirst($sched->frequency->value) }}
                                    </span>
                                </td>
                                <td data-label="Recipients">
                                    <div class="d-flex flex-wrap justify-content-end justify-content-md-start gap-1">
                                        @foreach($sched->recipients as $email)
                                            <span class="badge bg-light text-muted border small text-break">{{ $email }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td data-label="Status">
                                    <div class="form-check form-switch mb-0 d-inline-block">
                                        <input class="form-check-input" type="checkbox" wire:click="toggleSchedule({{ $sched->id }})" {{ $sched->is_active ? 'checked' : '' }} aria-label="Toggle schedule">
                                    </div>
                                </td>
                                <td data-label="Last Sent">
                                    <span class="small text-muted">{{ $sched->last_sent_at?->format('M d, Y') ?? 'Never' }}</span>
                                </td>
                                <td class="text-end" data-label="Actions">
                                    <button wire:click="toggleSchedule({{ $sched->id }})" class="btn btn-xs btn-outline-secondary">
                                        {{ $sched->is_active ? 'Pause' : 'Activate' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-clock-history fs-1 d-block mb-2 text-secondary"></i>
                                    <h6 class="fw-semibold">No automated schedules configured</h6>
                                    <p class="small mb-3">Set up automatic weekly or monthly report generation sent directly to clients.</p>
                                    <button wire:click="openScheduleModal" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Add Dispatch Schedule
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tbody wire:loading.delay wire:target="selectedClientId, formatFilter, activeTab">
                        @for($i = 0; $i < 4; $i++)
                            <tr>
                                <td>
                                    <div class="skeleton-box mb-1" style="width: 70%; height: 16px;"></div>
                                    <div class="skeleton-box" style="width: 40%; height: 12px;"></div>
                                </td>
                                <td><div class="skeleton-box skeleton-pill" style="width: 80px; height: 20px;"></div></td>
                                <td><div class="skeleton-box skeleton-pill" style="width: 60px; height: 20px;"></div></td>
                                <td><div class="skeleton-box" style="width: 110px; height: 14px;"></div></td>
                                <td><div class="skeleton-box skeleton-pill" style="width: 70px; height: 20px;"></div></td>
                                <td><div class="skeleton-box" style="width: 65px; height: 14px;"></div></td>
                                <td class="text-end"><div class="skeleton-box" style="width: 80px; height: 28px; border-radius: 6px;"></div></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Generate Report Modal -->
    @if($showCreateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header border-bottom py-3">
                        <h6 class="modal-title fw-bold text-dark mb-0">
                            <i class="bi bi-file-earmark-plus text-primary me-2"></i> Generate Client Performance Report
                        </h6>
                        <button type="button" class="btn-close" wire:click="closeCreateModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Report Title <span class="text-danger">*</span></label>
                                <input type="text" wire:model="reportTitle" class="form-control form-control-sm" placeholder="e.g. Q4 Executive Social Review">
                                @error('reportTitle') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Target Client</label>
                                <select wire:model="modalClientId" class="form-select form-select-sm">
                                    <option value="">All Clients (Workspace Summary)</option>
                                    @foreach($clients as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Export Format <span class="text-danger">*</span></label>
                                <select wire:model="modalFormat" class="form-select form-select-sm">
                                    <option value="pdf">PDF Executive Summary (Print-Ready)</option>
                                    <option value="csv">CSV Raw Data Export</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Reporting Timeframe</label>
                                <div class="row g-2 mb-2" role="group">
                                    <div class="col-6 col-sm-3">
                                        <input type="radio" class="btn-check" wire:model.live="datePreset" value="7d" id="preset7d">
                                        <label class="btn btn-sm btn-outline-secondary w-100" for="preset7d">Last 7 Days</label>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <input type="radio" class="btn-check" wire:model.live="datePreset" value="30d" id="preset30d">
                                        <label class="btn btn-sm btn-outline-secondary w-100" for="preset30d">Last 30 Days</label>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <input type="radio" class="btn-check" wire:model.live="datePreset" value="90d" id="preset90d">
                                        <label class="btn btn-sm btn-outline-secondary w-100" for="preset90d">Last 90 Days</label>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <input type="radio" class="btn-check" wire:model.live="datePreset" value="custom" id="presetCustom">
                                        <label class="btn btn-sm btn-outline-secondary w-100" for="presetCustom">Custom</label>
                                    </div>
                                </div>

                                @if($datePreset === 'custom')
                                    <div class="row g-2">
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label small text-muted">From Date</label>
                                            <input type="date" wire:model="customDateFrom" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label small text-muted">To Date</label>
                                            <input type="date" wire:model="customDateTo" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="col-12"><hr class="my-2"></div>

                            <!-- White-Label Branding Section -->
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold">White-Label Agency Brand Name</label>
                                <input type="text" wire:model="agencyName" class="form-control form-control-sm" placeholder="Your Agency Name">
                                <small class="text-muted" style="font-size: 0.75rem;">Appears on top of the generated client report cover & headers.</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Primary Brand Color</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" wire:model="brandColor" class="form-control form-control-color form-control-sm" style="width: 48px;">
                                    <input type="text" wire:model="brandColor" class="form-control form-control-sm" placeholder="#4f46e5">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="closeCreateModal">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center" wire:click="generateReport" wire:loading.attr="disabled">
                            <span wire:loading.remove><i class="bi bi-file-earmark-check me-1"></i> Generate & Compile</span>
                            <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span> Compiling Data...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Automated Schedule Modal -->
    @if($showScheduleModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header border-bottom py-3">
                        <h6 class="modal-title fw-bold text-dark mb-0">
                            <i class="bi bi-clock-history text-primary me-2"></i> Schedule Automated Dispatch
                        </h6>
                        <button type="button" class="btn-close" wire:click="closeScheduleModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Schedule Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="scheduleName" class="form-control form-control-sm" placeholder="e.g. Monthly Client Report — Pizza House">
                            @error('scheduleName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Target Client</label>
                            <select wire:model="scheduleClientId" class="form-select form-select-sm">
                                <option value="">All Clients</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Dispatch Frequency <span class="text-danger">*</span></label>
                            <select wire:model="scheduleFrequency" class="form-select form-select-sm">
                                <option value="weekly">Weekly (Every Monday Morning)</option>
                                <option value="monthly">Monthly (1st Day of Each Month)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Recipients (Email Addresses) <span class="text-danger">*</span></label>
                            <textarea wire:model="scheduleRecipients" class="form-control form-control-sm" rows="3" placeholder="client@example.com, marketing@client.com"></textarea>
                            <small class="text-muted" style="font-size: 0.75rem;">Comma-separated email addresses.</small>
                            @error('scheduleRecipients') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="closeScheduleModal">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary" wire:click="saveSchedule">
                            <i class="bi bi-check-lg me-1"></i> Save Schedule
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

