<div class="content-calendar-wrapper">
    <!-- Top Action & Filter Bar -->
    <div class="card-frosted p-3 p-md-4 mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <h4 class="mb-0 fw-bold d-flex align-items-center text-dark" style="letter-spacing: -0.02em;">
                    <i class="bi bi-calendar3 me-2 text-dark"></i> Content Calendar
                </h4>
                <small class="text-muted">Interactive schedule, queue timeline & publishing manager</small>
            </div>

            <div class="col-md-8">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    <!-- Client Filter -->
                    <div style="min-width: 150px;">
                        <select wire:model.live="selectedClientId" class="form-select form-select-sm rounded-pill px-3 py-2 border bg-white shadow-xs">
                            <option value="">All Clients</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Platform Filter -->
                    <div style="min-width: 140px;">
                        <select wire:model.live="selectedPlatform" class="form-select form-select-sm rounded-pill px-3 py-2 border bg-white shadow-xs">
                            <option value="">All Platforms</option>
                            @foreach($platforms as $p)
                                <option value="{{ $p->value }}">{{ $p->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div style="min-width: 130px;">
                        <select wire:model.live="selectedStatus" class="form-select form-select-sm rounded-pill px-3 py-2 border bg-white shadow-xs">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s->value }}">{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Create Post CTA -->
                    <a href="{{ route('composer') }}" class="btn-black-pill text-decoration-none">
                        <i class="bi bi-plus-lg"></i> <span>Create Post</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Flash notification -->
        @if($feedbackMessage)
            <div class="alert alert-{{ $feedbackType }} alert-dismissible fade show mt-3 mb-0 py-2 d-flex align-items-center rounded-3" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <div>{{ $feedbackMessage }}</div>
                <button type="button" class="btn-close py-2" wire:click="$set('feedbackMessage', null)"></button>
            </div>
        @endif
    </div>

    <!-- Calendar Card Canvas -->
    <div class="card-frosted p-3 p-md-4 mb-4">
        <div 
            x-data="calendarHandler($wire)" 
            x-init="initCalendar()"
            wire:ignore
        >
            <div id="fullcalendar-mount" class="fc-theme-custom"></div>
        </div>
    </div>

    <!-- Post Variant Details & Rescheduling Modal -->
    @if($showDetailModal && $selectedVariant)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $selectedVariant->socialProfile?->platform?->cssClass() ?? 'badge-platform-facebook' }} px-2 py-1">
                                <i class="bi {{ $selectedVariant->socialProfile?->platform?->icon() }} me-1"></i>
                                {{ $selectedVariant->socialProfile?->platform?->label() }}
                            </span>
                            <h6 class="modal-title fw-bold mb-0">
                                {{ $selectedVariant->socialProfile?->name ?? 'Social Profile' }}
                            </h6>
                            <span class="badge {{ $selectedVariant->status->badgeClass() }} ms-2">
                                {{ $selectedVariant->status->label() }}
                            </span>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeVariantModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Left: Content & Media Preview -->
                            <div class="col-md-7 border-end">
                                <div class="mb-3">
                                    <div class="small text-muted mb-1 fw-medium">Client</div>
                                    <div class="fw-semibold text-dark">
                                        <i class="bi bi-building me-1 text-primary"></i>
                                        {{ $selectedVariant->post?->client?->name ?? 'Default Workspace Client' }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="small text-muted mb-1 fw-medium">Effective Caption</div>
                                    <div class="p-3 bg-light rounded text-dark fs-6" style="white-space: pre-wrap; max-height: 200px; overflow-y: auto;">
                                        {{ $selectedVariant->getEffectiveCaption() ?: 'No caption provided.' }}
                                    </div>
                                </div>

                                @if($selectedVariant->post?->destination_link)
                                    <div class="mb-3">
                                        <div class="small text-muted mb-1 fw-medium">Destination Link</div>
                                        <a href="{{ $selectedVariant->post->destination_link }}" target="_blank" class="text-decoration-none text-truncate d-block text-primary small">
                                            <i class="bi bi-link-45deg me-1"></i>{{ $selectedVariant->post->destination_link }}
                                        </a>
                                    </div>
                                @endif

                                @if($selectedVariant->mediaAssets->isNotEmpty())
                                    <div class="mb-2">
                                        <div class="small text-muted mb-2 fw-medium">Attached Media ({{ $selectedVariant->mediaAssets->count() }})</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($selectedVariant->mediaAssets as $media)
                                                <img src="{{ $media->public_url }}" alt="Media" class="rounded border object-fit-cover shadow-sm" style="width: 72px; height: 72px;">
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($selectedVariant->status === \App\Enums\PostStatus::FAILED && $selectedVariant->error_message)
                                    <div class="alert alert-danger p-3 mt-3 mb-0 rounded-3 border-danger shadow-xs">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-exclamation-octagon-fill fs-5 text-danger flex-shrink-0 mt-1"></i>
                                            <div>
                                                <h6 class="fw-bold mb-1 text-danger">Publishing Failed</h6>
                                                <div class="small text-dark mb-2">
                                                    {{ $selectedVariant->error_message }}
                                                </div>
                                                @if(str_contains($selectedVariant->error_message, '200') || str_contains(strtolower($selectedVariant->error_message), 'permission'))
                                                    <div class="p-2 bg-white bg-opacity-75 rounded border border-danger-subtle small mt-2">
                                                        <strong class="d-block text-dark mb-1"><i class="bi bi-lightbulb-fill text-warning me-1"></i> How to fix:</strong>
                                                        <ol class="mb-1 ps-3 text-secondary" style="font-size: 0.8rem;">
                                                            <li>Open <a href="https://developers.facebook.com/apps" target="_blank" class="fw-bold text-decoration-underline text-danger">developers.facebook.com</a> & select your App.</li>
                                                            <li>Go to <strong>Use Cases ➡️ Manage and access Pages</strong> & add <strong>pages_manage_posts</strong>.</li>
                                                            <li>Go to <strong>App Roles ➡️ Roles</strong> and ensure your Facebook profile is listed as <strong>Admin</strong> or <strong>Developer</strong>.</li>
                                                            <li>Go to <a href="{{ route('social-accounts.index') }}" class="fw-bold text-decoration-underline text-primary">Social Accounts</a> and click <strong>Reconnect</strong>.</li>
                                                        </ol>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($selectedVariant->status === \App\Enums\PostStatus::PUBLISHED && $selectedVariant->external_post_url)
                                    <div class="mt-3">
                                        <a href="{{ $selectedVariant->external_post_url }}" target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> View Live Post on {{ $selectedVariant->socialProfile?->platform?->label() }}
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- Right: Reschedule & Actions -->
                            <div class="col-md-5">
                                <h6 class="fw-bold text-dark mb-3">Schedule & Timing</h6>

                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Date</label>
                                    <input type="date" wire:model="rescheduleDate" class="form-control form-control-sm">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Time (24-hour)</label>
                                    <input type="time" wire:model="rescheduleTime" class="form-control form-control-sm">
                                </div>

                                <button wire:click="saveReschedule" class="btn btn-primary btn-sm w-100 mb-3 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-calendar-check me-1"></i> Save New Schedule
                                </button>

                                <hr>

                                @if($selectedVariant->status === \App\Enums\PostStatus::FAILED)
                                    <div class="mb-2">
                                        <button wire:click="retryVariant({{ $selectedVariant->id }})" class="btn btn-danger btn-sm w-100 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-arrow-repeat me-1"></i> Retry Publishing Now
                                        </button>
                                        <small class="text-muted d-block mt-1 text-center" style="font-size: 0.75rem;">
                                            Dispatches worker to re-attempt provider publish immediately.
                                        </small>
                                    </div>
                                @endif

                                <div class="mt-3">
                                    <a href="{{ route('composer') }}" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="bi bi-pencil me-1"></i> Open in Full Composer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="closeVariantModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('calendarHandler', (wire) => ({
        calendar: null,

        initCalendar() {
            const calendarEl = document.getElementById('fullcalendar-mount');
            if (!calendarEl || !window.FullCalendar) return;

            const { Calendar, dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin } = window.FullCalendar;

            this.calendar = new Calendar(calendarEl, {
                plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    week: 'Week',
                    day: 'Day',
                    list: 'Agenda'
                },
                navLinks: true,
                editable: true,
                selectable: true,
                nowIndicator: true,
                dayMaxEvents: 3,
                events: (info, successCallback, failureCallback) => {
                    wire.getEvents(info.startStr, info.endStr)
                        .then(events => successCallback(events))
                        .catch(err => {
                            console.error('Failed to load calendar events', err);
                            failureCallback(err);
                        });
                },
                eventClick: (info) => {
                    info.jsEvent.preventDefault();
                    wire.openVariantModal(parseInt(info.event.id));
                },
                eventDrop: (info) => {
                    const variantId = parseInt(info.event.id);
                    const newDateIso = info.event.start.toISOString();
                    wire.rescheduleVariantFromCalendar(variantId, newDateIso);
                }
            });

            this.calendar.render();

            // Refresh events when Livewire fires calendar-refresh
            window.addEventListener('calendar-refresh', () => {
                this.calendar.refetchEvents();
            });

            // Watch filters
            wire.$watch('selectedClientId', () => this.calendar.refetchEvents());
            wire.$watch('selectedPlatform', () => this.calendar.refetchEvents());
            wire.$watch('selectedStatus', () => this.calendar.refetchEvents());
        }
    }));
});
</script>
@endpush

