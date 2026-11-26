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

                        @if (in_array($platform->value, ['facebook', 'instagram', 'linkedin', 'youtube', 'google_business', 'pinterest']))
                            @php
                                $currWs = app('current_workspace');
                                $cardDirectUrl = $currWs ? route('client-connect.oauth', ['uuid' => $currWs->uuid, 'platform' => $platform->value]) : '';
                            @endphp
                            <button type="button" class="btn btn-xs btn-light border text-muted px-2 py-1 rounded-pill" onclick="navigator.clipboard.writeText('{{ $cardDirectUrl }}'); this.innerHTML='<i class=\'bi bi-check text-success\'></i> Copied!'; setTimeout(() => this.innerHTML='<i class=\'bi bi-link-45deg\'></i> Link', 2000);" title="Copy Direct Client URL (Instant redirect to {{ $platform->label() }})">
                                <i class="bi bi-link-45deg"></i> Link
                            </button>
                        @endif
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
                        @endswitch
                    </p>

                    <div class="mt-auto">
                        @if (in_array($platform->value, ['facebook', 'instagram', 'linkedin', 'youtube', 'google_business', 'pinterest']))
                            @php
                                $currWs = app('current_workspace');
                                $cardDirectUrl = $currWs ? route('client-connect.oauth', ['uuid' => $currWs->uuid, 'platform' => $platform->value]) : '';
                                $platformWaText = 'Hi! Please click this secure link to connect your official ' . $platform->label() . ' account for ' . ($currWs?->name ?? 'our agency') . ': ' . $cardDirectUrl;
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('oauth.connect', ['platform' => $platform->value]) }}" class="btn btn-outline-primary btn-sm flex-grow-1 d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-plus-lg"></i>
                                    <span>Connect {{ $platform->label() }}</span>
                                </a>

                                <!-- Client Direct Connect Share Dropdown -->
                                <div class="dropdown">
                                    <button type="button" class="btn btn-primary btn-sm px-2 d-flex align-items-center justify-content-center" data-bs-toggle="dropdown" aria-expanded="false" title="Copy / Share Client Direct URL (Instant Redirect to {{ $platform->label() }})">
                                        <i class="bi bi-link-45deg fs-6"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 250px;">
                                        <li class="px-2 py-1">
                                            <span class="small fw-bold text-dark d-block">Client Direct URL</span>
                                            <span class="text-muted" style="font-size: 0.72rem;">Client taps this link and logs in directly with {{ $platform->label() }}.</span>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <button type="button" class="dropdown-item small d-flex align-items-center gap-2 py-2 rounded" onclick="navigator.clipboard.writeText('{{ $cardDirectUrl }}'); this.innerHTML='<i class=\'bi bi-check-lg text-success\'></i> <span>URL Copied!</span>'; setTimeout(() => this.innerHTML='<i class=\'bi bi-clipboard\'></i> <span>Copy Direct URL</span>', 2000);">
                                                <i class="bi bi-clipboard"></i>
                                                <span>Copy Direct URL</span>
                                            </button>
                                        </li>
                                        <li>
                                            <a class="dropdown-item small d-flex align-items-center gap-2 py-2 rounded text-success" href="https://api.whatsapp.com/send?text={{ urlencode($platformWaText) }}" target="_blank">
                                                <i class="bi bi-whatsapp"></i>
                                                <span>Share on WhatsApp</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item small d-flex align-items-center gap-2 py-2 rounded text-muted" href="mailto:?subject={{ urlencode('Connect your ' . $platform->label() . ' account') }}&body={{ urlencode($platformWaText) }}">
                                                <i class="bi bi-envelope"></i>
                                                <span>Share via Email</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
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

    <!-- Client Connect Share Modal -->
    @php
        $currWs = app('current_workspace');
        $clientConnectUrl = $currWs ? route('client-connect.show', ['uuid' => $currWs->uuid]) : '';
        $waMessage = 'Hi! Please click this link to securely connect your official Facebook, Instagram, or LinkedIn accounts so we can manage your social media marketing: ' . $clientConnectUrl;
    @endphp
    <div class="modal fade" id="clientConnectModal" tabindex="-1" aria-labelledby="clientConnectModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="clientConnectModalLabel">
                        <i class="bi bi-link-45deg text-primary fs-4"></i>
                        <span>Client Social Connect Link</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Send this secure link to your client via WhatsApp or Email. Your client can open the link on their mobile or PC, log into their Facebook, Instagram, or LinkedIn, and authorize access directly. <strong>You will never need to ask them for their passwords!</strong>
                    </p>

                    <label class="form-label small fw-semibold text-dark">Shareable Client Connect URL:</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control form-control-sm bg-light" id="clientConnectUrlInput" value="{{ $clientConnectUrl }}" readonly>
                        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" type="button" onclick="navigator.clipboard.writeText(document.getElementById('clientConnectUrlInput').value); this.innerHTML='<i class=\'bi bi-check2\'></i> Copied!'; setTimeout(() => this.innerHTML='<i class=\'bi bi-clipboard\'></i> Copy', 2000);">
                            <i class="bi bi-clipboard"></i>
                            <span>Copy</span>
                        </button>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($waMessage) }}" target="_blank" class="btn btn-sm btn-success d-inline-flex align-items-center gap-2">
                            <i class="bi bi-whatsapp"></i>
                            <span>Share on WhatsApp</span>
                        </a>
                        <a href="mailto:?subject={{ urlencode('Connect your social media accounts for ' . ($currWs?->name ?? 'our agency')) }}&body={{ urlencode($waMessage) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-envelope"></i>
                            <span>Share via Email</span>
                        </a>
                        <a href="{{ $clientConnectUrl }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <span>Preview Client Page</span>
                        </a>
                    </div>

                    <div class="border-top pt-3">
                        <h6 class="fw-bold text-dark small mb-1">
                            <i class="bi bi-people-fill text-primary me-1"></i>Want client to also log into PostSync dashboard?
                        </h6>
                        <p class="text-muted small mb-2">
                            If you want your client to log into PostSync to review the content calendar and approve posts, invite them as a team member with the <strong>Client Viewer</strong> role.
                        </p>
                        <a href="{{ route('team.index') }}" class="btn btn-sm btn-light border text-primary fw-semibold">
                            <i class="bi bi-arrow-right-circle me-1"></i>Go to Team Page to Invite Client
                        </a>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
