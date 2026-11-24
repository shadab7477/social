<div>
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-share text-primary me-2"></i>Social Accounts
            </h4>
            <p class="text-muted small mb-0">
                Connect and manage authorized social media profiles across official platforms.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" wire:click="connectDemoProfiles" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-lightning-charge-fill text-warning"></i>
                <span class="fw-semibold">⚡ Connect Demo Accounts (Sandbox)</span>
            </button>
        </div>
    </div>

    @if (!config('services.facebook.client_id'))
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-start gap-3 mb-4">
            <i class="bi bi-info-circle-fill fs-5 text-primary mt-1"></i>
            <div class="small flex-grow-1">
                <strong class="d-block text-dark mb-1">Local Development Setup Notice</strong>
                <span>
                    Your <code>META_APP_ID</code> and <code>META_APP_SECRET</code> are currently empty in <code>.env</code>. To connect live Meta accounts, register an app at <a href="https://developers.facebook.com" target="_blank" class="alert-link text-decoration-underline">developers.facebook.com</a> and paste your credentials into <code>.env</code>.
                    <br>
                    <strong>Testing locally?</strong> Click the <strong>⚡ Connect Demo Accounts</strong> button above to immediately simulate authorized Facebook, Instagram, and LinkedIn accounts for testing Post Composer, Calendar, Approvals, and Analytics!
                </span>
            </div>
        </div>
    @endif

    <!-- Active Connections & Profiles Section -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-check2-circle text-success me-2"></i>Connected Profiles & Pages ({{ $connections->pluck('profiles')->flatten()->count() }})
            </h6>
        </div>
        <div class="card-body p-0">
            @if ($connections->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th class="ps-3">Profile / Page</th>
                                <th>Platform</th>
                                <th>Assigned Client</th>
                                <th>Connection Health</th>
                                <th>Publishing</th>
                                <th>Last Sync</th>
                                <th class="pe-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($connections as $conn)
                                @forelse ($conn->profiles as $profile)
                                    @php
                                        $profPlat = $profile->platform instanceof \App\Enums\SocialPlatform ? $profile->platform : \App\Enums\SocialPlatform::tryFrom($profile->platform);
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($profile->avatar_url)
                                                    <img src="{{ $profile->avatar_url }}" alt="{{ $profile->name }}" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                                                @else
                                                    <span class="platform-icon {{ $profPlat?->value ?? 'facebook' }}" style="width: 36px; height: 36px;">
                                                        <i class="bi {{ $profPlat?->icon() ?? 'bi-share' }}"></i>
                                                    </span>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $profile->name }}</div>
                                                    @if ($profile->username)
                                                        <div class="text-muted small">&#64;{{ $profile->username }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="platform-icon {{ $profPlat?->value ?? 'facebook' }}" style="width: 24px; height: 24px; font-size: 0.75rem;">
                                                <i class="bi {{ $profPlat?->icon() ?? 'bi-share' }}"></i>
                                            </span>
                                            <span class="small ms-1 fw-medium">{{ $profPlat?->label() }}</span>
                                        </td>
                                        <td>
                                            @if ($profile->client)
                                                <span class="badge bg-light text-dark border">{{ $profile->client->name }}</span>
                                            @else
                                                <span class="text-muted small fst-italic">Workspace Default</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $conn->status->badgeClass() }}">
                                                {{ $conn->status->label() }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" wire:click="toggleProfilePublishing({{ $profile->id }})" class="btn btn-sm {{ $profile->publishing_enabled ? 'btn-outline-success' : 'btn-outline-secondary' }} py-0 px-2" style="font-size: 0.75rem;">
                                                <i class="bi {{ $profile->publishing_enabled ? 'bi-check2' : 'bi-pause' }} me-1"></i>
                                                {{ $profile->publishing_enabled ? 'Enabled' : 'Paused' }}
                                            </button>
                                        </td>
                                        <td class="small text-muted">
                                            {{ $conn->last_sync_at ? $conn->last_sync_at->diffForHumans() : '—' }}
                                        </td>
                                        <td class="pe-3 text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button wire:click="deleteProfile({{ $profile->id }})" wire:confirm="Are you sure you want to remove this profile from your workspace?" class="btn btn-light border text-danger" title="Remove Profile">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="ps-3 py-3 text-muted small">
                                            <i class="bi bi-info-circle me-1"></i> Account '{{ $conn->account_name }}' is authorized, but no pages or channels have been selected yet.
                                        </td>
                                    </tr>
                                @endforelse
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty-state 
                    icon="bi-share"
                    title="No social accounts connected"
                    description="Connect your social media accounts below to start scheduling and publishing posts."
                />
            @endif
        </div>
    </div>

    <!-- Available Platforms Grid -->
    <h5 class="fw-bold text-dark mb-3">Available Platforms</h5>
    <div class="row g-3">
        @foreach ($allPlatforms as $platform)
            @php
                $connectedCount = $platformCounts[$platform->value] ?? 0;
            @endphp
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card h-100 border shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="platform-icon {{ $platform->cssClass() }}">
                                <i class="bi {{ $platform->icon() }}"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $platform->label() }}</h6>
                                @if ($connectedCount > 0)
                                    <span class="text-success small fw-medium">
                                        <i class="bi bi-check-circle-fill"></i> {{ $connectedCount }} Profile(s)
                                    </span>
                                @else
                                    <span class="text-muted small">Not connected</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small mb-4" style="min-height: 38px;">
                        @switch($platform->value)
                            @case('facebook') Direct publishing for Pages, Reels & Feed. @break
                            @case('instagram') Professional Business & Creator single images, reels, carousels. @break
                            @case('linkedin') Post to personal profiles & company organization pages. @break
                            @case('youtube') Resumable video uploads with daily quota monitoring. @break
                            @case('tiktok') Official Content Posting API with creator auditing. @break
                            @case('pinterest') Pin images, videos & destination links to boards. @break
                            @case('x') OAuth 2.0 PKCE tweet posting & rate-limit safety. @break
                            @case('threads') Official Threads Graph API container publishing. @break
                            @case('google_business') Multi-location local posts, offers and updates. @break
                            @case('bluesky') AT Protocol decentralized federated posting. @break
                        @endswitch
                    </p>

                    <div class="mt-auto">
                        @if (in_array($platform->value, ['facebook', 'instagram', 'linkedin', 'youtube', 'google_business']))
                            <a href="{{ route('oauth.connect', ['platform' => $platform->value]) }}" class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-plus-lg"></i>
                                <span>Connect {{ $platform->label() }}</span>
                            </a>
                        @else
                            <button class="btn btn-light border btn-sm w-100 text-muted" disabled>
                                <i class="bi bi-clock-history me-1"></i> Phase 15
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Post-OAuth Profile Selection Modal -->
    @if ($showProfileModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow border-0">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fs-6 fw-bold mb-0">Select Social Profiles / Pages to Import</h5>
                            <span class="text-muted small">Choose the specific pages or channels you want to manage.</span>
                        </div>
                        <button type="button" class="btn-close" wire:click="$set('showProfileModal', false)"></button>
                    </div>
                    <form wire:submit="importSelectedProfiles">
                        <div class="modal-body">
                            <!-- Client Assignment Selector -->
                            <div class="mb-4 bg-light p-3 rounded-3">
                                <label class="form-label small fw-semibold text-dark">Assign to Agency Client (Optional):</label>
                                <select class="form-select bg-white" wire:model="assignClientId">
                                    <option value="">No Client (Workspace General / Agency Self)</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->client_code }})</option>
                                    @endforeach
                                </select>
                                <div class="form-text small">Attaching imported profiles to a client isolates them for client reporting and client reviewer access.</div>
                            </div>

                            <!-- Discovered Profiles List -->
                            <label class="form-label small fw-semibold mb-2">Discovered Accounts ({{ count($discoveredProfiles) }} available):</label>
                            <div class="list-group">
                                @forelse ($discoveredProfiles as $item)
                                    <label class="list-group-item d-flex align-items-center justify-content-between p-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <input class="form-check-input flex-shrink-0" type="checkbox" value="{{ $item['profileId'] }}" wire:model="selectedProfileIds">
                                            @if ($item['avatarUrl'])
                                                <img src="{{ $item['avatarUrl'] }}" alt="{{ $item['name'] }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <span class="platform-icon {{ $item['platform'] }}" style="width: 40px; height: 40px;">
                                                    <i class="bi {{ \App\Enums\SocialPlatform::tryFrom($item['platform'])?->icon() }}"></i>
                                                </span>
                                            @endif
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $item['name'] }}</div>
                                                <div class="text-muted small">
                                                    <span class="badge bg-light text-dark border me-1">{{ ucfirst($item['platform']) }}</span>
                                                    @if (!empty($item['username'])) &#64;{{ $item['username'] }} @endif
                                                </div>
                                            </div>
                                        </div>
                                        <span class="badge bg-success-subtle text-success small">Discovered</span>
                                    </label>
                                @empty
                                    <div class="p-4 text-center text-muted small">
                                        No pages or accounts were discovered for this login.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-sm btn-secondary" wire:click="$set('showProfileModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Import Selected Profiles</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
