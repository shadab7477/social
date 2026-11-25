<div>
    <!-- Page Title -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-0">
                <i class="bi bi-pencil-square text-primary me-2"></i>Content Composer
            </h4>
            <p class="text-muted small mb-0">Create, customize for each network, preview in real time, and schedule content.</p>
        </div>
    </div>

    <!-- 3-Column Composer Grid -->
    <div class="composer-grid">
        <!-- ========================================== -->
        <!-- 1. LEFT PANEL: TARGETS & BASE CONTENT       -->
        <!-- ========================================== -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-bullseye text-primary me-2"></i>1. Publishing Targets
                </h6>
            </div>
            <div class="card-body p-3">
                <!-- Client Filter Dropdown -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.75rem;">Agency Client</label>
                    <select class="form-select form-select-sm bg-light" wire:model.live="selectedClientId">
                        <option value="">All Clients / Agency</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->client_code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Profile Target Checkboxes -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-semibold text-muted text-uppercase mb-0" style="font-size: 0.75rem;">Select Profiles ({{ count($selectedProfileIds) }})</label>
                        <div class="btn-group btn-group-sm">
                            <button type="button" wire:click="selectAllProfiles" class="btn btn-link p-0 text-decoration-none small" style="font-size: 0.75rem;">All</button>
                            <span class="text-muted mx-1">|</span>
                            <button type="button" wire:click="deselectAllProfiles" class="btn btn-link p-0 text-decoration-none small text-secondary" style="font-size: 0.75rem;">None</button>
                        </div>
                    </div>

                    <div class="list-group list-group-flush border rounded-3 p-1" style="max-height: 240px; overflow-y: auto;">
                        @forelse ($availableProfiles as $profile)
                            @php
                                $isSelected = in_array($profile->id, $selectedProfileIds, true);
                                $pEnum = $profile->platform instanceof \App\Enums\SocialPlatform ? $profile->platform : \App\Enums\SocialPlatform::tryFrom($profile->platform);
                            @endphp
                            <label class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded-2 py-2 px-2 cursor-pointer {{ $isSelected ? 'bg-primary-subtle' : '' }}" style="cursor: pointer;">
                                <input class="form-check-input flex-shrink-0 m-0" type="checkbox" value="{{ $profile->id }}" wire:click="toggleProfile({{ $profile->id }})" {{ $isSelected ? 'checked' : '' }}>
                                <span class="platform-icon {{ $pEnum?->value ?? 'facebook' }}" style="width: 26px; height: 26px; font-size: 0.8rem;">
                                    <i class="bi {{ $pEnum?->icon() }}"></i>
                                </span>
                                <div class="text-truncate flex-grow-1" style="max-width: 170px;">
                                    <div class="fw-semibold text-dark small text-truncate">{{ $profile->name }}</div>
                                    @if ($profile->client)
                                        <div class="text-muted text-truncate" style="font-size: 0.7rem;">{{ $profile->client->name }}</div>
                                    @endif
                                </div>
                            </label>
                        @empty
                            <div class="p-3 text-center text-muted small">
                                No profiles connected yet. <br>
                                <a href="{{ route('social-accounts.index') }}" class="small">Connect Account</a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <hr class="my-3">

                <!-- Master Base Caption -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-semibold text-muted text-uppercase mb-0" style="font-size: 0.75rem;">Master Base Caption</label>
                        <button type="button" wire:click="openAiModal('base')" class="btn btn-xs btn-outline-primary d-inline-flex align-items-center gap-1 shadow-xs" style="font-size: 0.75rem;">
                            <i class="bi bi-stars text-warning"></i> AI Assistant
                        </button>
                    </div>
                    <textarea class="form-control" wire:model.live.debounce.250ms="baseCaption" rows="4" placeholder="What's happening? Write your core post caption here..."></textarea>
                    <div class="form-text small" style="font-size: 0.75rem;">This caption automatically synchronizes across all target platforms unless customized.</div>
                </div>

                <!-- Destination Link -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.75rem;">Destination Link (Optional)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                        <input type="url" class="form-control" wire:model.live="destinationLink" placeholder="https://yourwebsite.com/promo">
                    </div>
                </div>

                <!-- Media Asset Selector -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-semibold text-muted text-uppercase mb-0" style="font-size: 0.75rem;">Attached Media ({{ count($selectedMediaAssetIds) }})</label>
                        <button type="button" wire:click="openMediaModal" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                            <i class="bi bi-images me-1"></i> Browse Library
                        </button>
                    </div>

                    @if ($selectedMediaAssets->count() > 0)
                        <div class="d-flex gap-2 flex-wrap p-2 border rounded-3 bg-light">
                            @foreach ($selectedMediaAssets as $media)
                                <div class="position-relative border rounded overflow-hidden shadow-xs" style="width: 60px; height: 60px;">
                                    @if ($media->isImage() || $media->media_type === 'gif')
                                        <img src="{{ $media->public_url }}" class="w-100 h-100" style="object-fit: cover;">
                                    @else
                                        <div class="w-100 h-100 bg-dark text-white d-flex align-items-center justify-content-center">
                                            <i class="bi bi-play-fill fs-5"></i>
                                        </div>
                                    @endif
                                    <button type="button" wire:click="removeMediaAsset({{ $media->id }})" class="btn btn-danger position-absolute top-0 end-0 p-0 d-flex align-items-center justify-content-center rounded-circle" style="width: 18px; height: 18px; font-size: 0.65rem;">
                                        &times;
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div wire:click="openMediaModal" class="border border-dashed rounded-3 p-3 text-center bg-light cursor-pointer text-muted small" style="cursor: pointer;">
                            <i class="bi bi-cloud-arrow-up fs-4 d-block text-secondary mb-1"></i>
                            Click to select images or videos from Media Library
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 2. CENTER PANEL: PLATFORM VARIANT CUSTOMIZER -->
        <!-- ========================================== -->
        <div class="card border-0 shadow-sm">
            <!-- Variant Tabs Header -->
            <div class="card-header bg-white p-2">
                <ul class="nav nav-pills small gap-1">
                    <li class="nav-item">
                        <button type="button" wire:click="setTab('base')" class="nav-link py-1 px-3 {{ $activeTab === 'base' ? 'active' : '' }}">
                            <i class="bi bi-globe me-1"></i> Base / All
                        </button>
                    </li>
                    @foreach ($availableProfiles as $profile)
                        @if (in_array($profile->id, $selectedProfileIds, true))
                            @php
                                $tabPEnum = $profile->platform instanceof \App\Enums\SocialPlatform ? $profile->platform : \App\Enums\SocialPlatform::tryFrom($profile->platform);
                            @endphp
                            <li class="nav-item">
                                <button type="button" wire:click="setTab('{{ $profile->id }}')" class="nav-link py-1 px-2 d-flex align-items-center gap-1 {{ $activeTab == (string)$profile->id ? 'active' : '' }}">
                                    <span class="platform-icon {{ $tabPEnum?->value ?? 'facebook' }}" style="width: 18px; height: 18px; font-size: 0.65rem;">
                                        <i class="bi {{ $tabPEnum?->icon() }}"></i>
                                    </span>
                                    <span class="small text-truncate" style="max-width: 90px;">{{ $profile->name }}</span>
                                    @if (!empty($customizedFlags[$profile->id]))
                                        <span class="badge bg-warning rounded-circle p-1" title="Tailored Variant"></span>
                                    @endif
                                </button>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            <div class="card-body p-3">
                @if ($activeTab === 'base')
                    <!-- Master Tab Description -->
                    <div class="alert alert-light border small text-muted mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle text-primary fs-5"></i>
                        <div>
                            You are editing the <strong>Master Base Campaign</strong>. Content typed here cascades to all target profiles. Switch to individual tabs above to write network-specific captions!
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Master Caption Editor</label>
                        <textarea class="form-control" wire:model.live.debounce.250ms="baseCaption" rows="9" placeholder="Enter post caption..."></textarea>
                    </div>
                @else
                    <!-- Individual Profile Variant Tab -->
                    @php
                        $currentTabProfile = SocialProfile::find((int)$activeTab);
                    @endphp

                    @if ($currentTabProfile)
                        @php
                            $currEnum = $currentTabProfile->platform instanceof \App\Enums\SocialPlatform ? $currentTabProfile->platform : \App\Enums\SocialPlatform::tryFrom($currentTabProfile->platform);
                        @endphp
                        <div class="d-flex align-items-center justify-content-between p-2 mb-3 bg-light rounded-3 border">
                            <div class="d-flex align-items-center gap-2">
                                <span class="platform-icon {{ $currEnum?->value ?? 'facebook' }}" style="width: 30px; height: 30px;">
                                    <i class="bi {{ $currEnum?->icon() }}"></i>
                                </span>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark small">{{ $currentTabProfile->name }}</h6>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $currEnum?->label() }}</span>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="customCheck{{ $currentTabProfile->id }}" wire:click="toggleCustomizeVariant({{ $currentTabProfile->id }})" {{ !empty($customizedFlags[$currentTabProfile->id]) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-semibold" for="customCheck{{ $currentTabProfile->id }}">
                                    Customize for {{ $currEnum?->label() }}
                                </label>
                            </div>
                        </div>

                        <!-- Caption Area -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label small fw-semibold mb-0">
                                        {{ !empty($customizedFlags[$currentTabProfile->id]) ? 'Tailored Caption' : 'Inherited Base Caption' }}
                                    </label>
                                    @if (!empty($customizedFlags[$currentTabProfile->id]))
                                        <button type="button" wire:click="openAiModal('{{ $currentTabProfile->id }}')" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.7rem;">
                                            <i class="bi bi-stars text-warning"></i> Tailor with AI
                                        </button>
                                    @endif
                                </div>
                                @php
                                    $effectiveCap = $this->getEffectiveCaption($currentTabProfile);
                                    $charCount = mb_strlen($effectiveCap);
                                    $limit = $this->getCharacterLimit($currentTabProfile);
                                    $isOver = $charCount > $limit;
                                @endphp
                                <span class="badge {{ $isOver ? 'bg-danger' : 'bg-light text-dark border' }} small">
                                    {{ $charCount }} / {{ $limit }} chars
                                </span>
                            </div>

                            @if (!empty($customizedFlags[$currentTabProfile->id]))
                                <textarea class="form-control @if($isOver) is-invalid @endif" wire:model.live.debounce.250ms="customCaptions.{{ $currentTabProfile->id }}" rows="8" placeholder="Type custom caption for {{ $currentTabProfile->name }}..."></textarea>
                            @else
                                <textarea class="form-control bg-light" rows="8" readonly disabled>{{ $baseCaption }}</textarea>
                                <div class="form-text small">Toggle switch above to write independent text, add platform hashtags, or tailor tone.</div>
                            @endif
                        </div>

                        <!-- Platform-Specific Settings Accordion -->
                        @php
                            $platKey = $currentTabProfile->platform instanceof \App\Enums\SocialPlatform ? $currentTabProfile->platform->value : $currentTabProfile->platform;
                        @endphp

                        @if ($platKey === 'youtube')
                            <!-- YouTube Publishing & Content Hub Settings -->
                            <div class="card border p-3 bg-light rounded-3 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="fw-bold small mb-0 text-dark d-flex align-items-center gap-2">
                                        <i class="bi bi-youtube text-danger fs-5"></i> YouTube Video & Shorts Settings
                                    </h6>
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small">Official YouTube API</span>
                                </div>

                                <!-- Format Choice: Standard Video vs YouTube Shorts -->
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark mb-1">Select Upload Type</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div wire:click="$set('platformSettings.{{ $currentTabProfile->id }}.post_type', 'video')" 
                                                 class="p-2 border rounded-3 text-center cursor-pointer transition {{ ($platformSettings[$currentTabProfile->id]['post_type'] ?? 'video') === 'video' ? 'border-danger bg-white shadow-xs' : 'bg-white opacity-75' }}" style="cursor: pointer;">
                                                <div class="fs-5 text-danger mb-1"><i class="bi bi-film"></i></div>
                                                <div class="small fw-bold text-dark">Standard Video</div>
                                                <small class="text-muted" style="font-size: 0.72rem;">16:9 Landscape / Custom Thumbnail</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div wire:click="$set('platformSettings.{{ $currentTabProfile->id }}.post_type', 'shorts')" 
                                                 class="p-2 border rounded-3 text-center cursor-pointer transition {{ ($platformSettings[$currentTabProfile->id]['post_type'] ?? '') === 'shorts' ? 'border-danger bg-white shadow-xs' : 'bg-white opacity-75' }}" style="cursor: pointer;">
                                                <div class="fs-5 text-danger mb-1"><i class="bi bi-lightning-charge-fill"></i></div>
                                                <div class="small fw-bold text-dark">YouTube Shorts ⚡</div>
                                                <small class="text-muted" style="font-size: 0.72rem;">9:16 Vertical &lt;60s / Auto #Shorts</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Video Title Input -->
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark mb-1">Video Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm rounded-2" 
                                           wire:model="platformSettings.{{ $currentTabProfile->id }}.title" 
                                           maxlength="100" 
                                           placeholder="Enter a compelling title for your YouTube video...">
                                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Max 100 characters.</small>
                                </div>

                                <!-- Custom Thumbnail (Standard Video) -->
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark mb-1">Custom Video Thumbnail</label>
                                    @if (!empty($platformSettings[$currentTabProfile->id]['thumbnail_url']))
                                        <div class="d-flex align-items-center gap-3 p-2 bg-white border rounded-3">
                                            <img src="{{ $platformSettings[$currentTabProfile->id]['thumbnail_url'] }}" class="rounded shadow-xs" style="width: 100px; height: 56px; object-fit: cover;" alt="Thumbnail">
                                            <div class="flex-grow-1">
                                                <span class="badge bg-success-subtle text-success small mb-1">Custom Thumbnail Attached</span>
                                                <div class="small text-muted text-truncate" style="max-width: 200px;">{{ basename($platformSettings[$currentTabProfile->id]['thumbnail_url']) }}</div>
                                            </div>
                                            <button type="button" wire:click="removeThumbnail({{ $currentTabProfile->id }})" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="d-flex gap-2">
                                            <button type="button" wire:click="openThumbnailModal({{ $currentTabProfile->id }})" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 rounded-pill px-3">
                                                <i class="bi bi-image"></i> Choose from Media Library
                                            </button>
                                            <label class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 rounded-pill px-3 mb-0 cursor-pointer">
                                                <i class="bi bi-upload"></i> Upload Image
                                                <input type="file" wire:model="directThumbnailUpload" wire:click="$set('selectingThumbnailForProfileId', {{ $currentTabProfile->id }})" class="d-none" accept="image/*">
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Recommended size: 1280x720 (16:9 aspect ratio, max 2MB).</small>
                                    @endif
                                </div>

                                <!-- Privacy & Category Row -->
                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">Privacy Status</label>
                                        <select class="form-select form-select-sm" wire:model="platformSettings.{{ $currentTabProfile->id }}.privacy_status">
                                            <option value="public">Public (Everyone can see)</option>
                                            <option value="unlisted">Unlisted (Anyone with link)</option>
                                            <option value="private">Private (Only you)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">Category</label>
                                        <select class="form-select form-select-sm" wire:model="platformSettings.{{ $currentTabProfile->id }}.category_id">
                                            <option value="22">People & Blogs</option>
                                            <option value="28">Science & Technology</option>
                                            <option value="27">Education</option>
                                            <option value="24">Entertainment</option>
                                            <option value="17">Sports</option>
                                            <option value="26">Howto & Style</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Tags -->
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-dark mb-1">Video Tags</label>
                                    <input type="text" class="form-control form-control-sm rounded-2" wire:model="platformSettings.{{ $currentTabProfile->id }}.tags" placeholder="tutorial, tech, reviews (comma-separated)">
                                </div>

                                <!-- Made for kids -->
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="ytKids{{ $currentTabProfile->id }}" wire:model="platformSettings.{{ $currentTabProfile->id }}.made_for_kids">
                                    <label class="form-check-label small" for="ytKids{{ $currentTabProfile->id }}">
                                        Yes, this video is made for kids (COPPA compliance)
                                    </label>
                                </div>
                            </div>
                        @elseif ($platKey === 'instagram')
                            <!-- Instagram Specific Settings -->
                            <div class="card border p-3 bg-light rounded-3 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold small mb-0 text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-instagram text-danger fs-5"></i> Instagram Post Format & Settings
                                    </h6>
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small">Professional API</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark mb-1">Select Upload Type</label>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <div wire:click="$set('platformSettings.{{ $currentTabProfile->id }}.post_type', 'feed')" 
                                                 class="p-2 border rounded-3 text-center cursor-pointer transition {{ ($platformSettings[$currentTabProfile->id]['post_type'] ?? 'feed') === 'feed' ? 'border-primary bg-white shadow-xs' : 'bg-white opacity-75' }}" style="cursor: pointer;">
                                                <div class="fs-5 text-primary mb-1"><i class="bi bi-grid-3x3"></i></div>
                                                <div class="small fw-bold text-dark">Feed Post</div>
                                                <small class="text-muted" style="font-size: 0.7rem;">Image or Video</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div wire:click="$set('platformSettings.{{ $currentTabProfile->id }}.post_type', 'reel')" 
                                                 class="p-2 border rounded-3 text-center cursor-pointer transition {{ ($platformSettings[$currentTabProfile->id]['post_type'] ?? '') === 'reel' ? 'border-primary bg-white shadow-xs' : 'bg-white opacity-75' }}" style="cursor: pointer;">
                                                <div class="fs-5 text-danger mb-1"><i class="bi bi-play-btn-fill"></i></div>
                                                <div class="small fw-bold text-dark">Instagram Reel</div>
                                                <small class="text-muted" style="font-size: 0.7rem;">9:16 Vertical Video</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div wire:click="$set('platformSettings.{{ $currentTabProfile->id }}.post_type', 'story')" 
                                                 class="p-2 border rounded-3 text-center cursor-pointer transition {{ ($platformSettings[$currentTabProfile->id]['post_type'] ?? '') === 'story' ? 'border-primary bg-white shadow-xs' : 'bg-white opacity-75' }}" style="cursor: pointer;">
                                                <div class="fs-5 text-warning mb-1"><i class="bi bi-circle"></i></div>
                                                <div class="small fw-bold text-dark">Story</div>
                                                <small class="text-muted" style="font-size: 0.7rem;">24 Hours</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(($platformSettings[$currentTabProfile->id]['post_type'] ?? 'feed') === 'reel')
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="igShareFeed{{ $currentTabProfile->id }}" wire:model="platformSettings.{{ $currentTabProfile->id }}.share_to_feed" checked>
                                        <label class="form-check-label small" for="igShareFeed{{ $currentTabProfile->id }}">
                                            Also share this Reel to your main Instagram Feed grid
                                        </label>
                                    </div>
                                @endif
                            </div>
                        @elseif ($platKey === 'facebook')
                            <!-- Facebook Specific Settings -->
                            <div class="card border p-3 bg-light rounded-3 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold small mb-0 text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-facebook text-primary fs-5"></i> Facebook Format
                                    </h6>
                                </div>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <div wire:click="$set('platformSettings.{{ $currentTabProfile->id }}.post_type', 'feed')" 
                                             class="p-2 border rounded-3 text-center cursor-pointer transition {{ ($platformSettings[$currentTabProfile->id]['post_type'] ?? 'feed') === 'feed' ? 'border-primary bg-white shadow-xs' : 'bg-white opacity-75' }}" style="cursor: pointer;">
                                            <div class="small fw-bold text-dark">Page Post</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div wire:click="$set('platformSettings.{{ $currentTabProfile->id }}.post_type', 'reel')" 
                                             class="p-2 border rounded-3 text-center cursor-pointer transition {{ ($platformSettings[$currentTabProfile->id]['post_type'] ?? '') === 'reel' ? 'border-primary bg-white shadow-xs' : 'bg-white opacity-75' }}" style="cursor: pointer;">
                                            <div class="small fw-bold text-dark">Facebook Reel</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div wire:click="$set('platformSettings.{{ $currentTabProfile->id }}.post_type', 'story')" 
                                             class="p-2 border rounded-3 text-center cursor-pointer transition {{ ($platformSettings[$currentTabProfile->id]['post_type'] ?? '') === 'story' ? 'border-primary bg-white shadow-xs' : 'bg-white opacity-75' }}" style="cursor: pointer;">
                                            <div class="small fw-bold text-dark">Story</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif ($platKey === 'pinterest')
                            <div class="card border p-3 bg-light rounded-3 mb-3">
                                <h6 class="fw-semibold small mb-2 text-dark"><i class="bi bi-gear me-1"></i> Pinterest Settings</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Target Board</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="Select or type Board Name">
                                </div>
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 3. RIGHT PANEL: LIVE PREVIEW & ACTIONS     -->
        <!-- ========================================== -->
        <div>
            <!-- Live Mock Preview Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                    <span class="small fw-bold text-dark"><i class="bi bi-eye text-primary me-1"></i> Live Network Preview</span>
                    @if ($previewProfile)
                        <span class="badge bg-light text-dark border small">{{ $previewProfile->platform instanceof \App\Enums\SocialPlatform ? $previewProfile->platform->label() : ucfirst($previewProfile->platform) }}</span>
                    @endif
                </div>
                <div class="card-body p-3">
                    @if ($previewProfile)
                        @php
                            $previewCaption = $this->getEffectiveCaption($previewProfile);
                            $firstMedia = $selectedMediaAssets->first();
                        @endphp

                        @switch($previewProfile->platform)
                            @case('facebook')
                                <!-- Facebook Mock Card -->
                                <div class="post-preview-card shadow-xs">
                                    <div class="preview-header">
                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                            {{ strtoupper(substr($previewProfile->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark small">{{ $previewProfile->name }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Just now &bull; <i class="bi bi-globe"></i></div>
                                        </div>
                                    </div>
                                    <div class="preview-body">
                                        {{ $previewCaption ?: 'Preview text will appear here...' }}
                                    </div>
                                    @if ($firstMedia)
                                        @if ($firstMedia->isImage() || $firstMedia->media_type === 'gif')
                                            <img src="{{ $firstMedia->public_url }}" class="preview-media">
                                        @else
                                            <div class="bg-dark text-white p-5 text-center">
                                                <i class="bi bi-play-circle fs-1"></i>
                                            </div>
                                        @endif
                                    @endif
                                    <div class="preview-footer">
                                        <span><i class="bi bi-hand-thumbs-up me-1"></i> Like</span>
                                        <span><i class="bi bi-chat me-1"></i> Comment</span>
                                        <span><i class="bi bi-share me-1"></i> Share</span>
                                    </div>
                                </div>
                                @break

                            @case('instagram')
                                <!-- Instagram Mock Card -->
                                <div class="post-preview-card shadow-xs">
                                    <div class="preview-header">
                                        <div class="avatar bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            {{ strtoupper(substr($previewProfile->username ?: $previewProfile->name, 0, 1)) }}
                                        </div>
                                        <div class="fw-semibold text-dark small">&#64;{{ $previewProfile->username ?: strtolower(str_replace(' ', '', $previewProfile->name)) }}</div>
                                    </div>
                                    @if ($firstMedia)
                                        <img src="{{ $firstMedia->public_url }}" class="preview-media" style="aspect-ratio: 1/1; object-fit: cover;">
                                    @else
                                        <div class="bg-light text-muted p-5 text-center border-top border-bottom small fst-italic">
                                            <i class="bi bi-image fs-2 d-block text-secondary mb-1"></i>
                                            Instagram requires an image or video
                                        </div>
                                    @endif
                                    <div class="preview-footer justify-content-start gap-3">
                                        <i class="bi bi-heart fs-6"></i>
                                        <i class="bi bi-chat fs-6"></i>
                                        <i class="bi bi-send fs-6"></i>
                                    </div>
                                    <div class="px-3 pb-3 small text-dark">
                                        <strong>&#64;{{ $previewProfile->username ?: 'account' }}</strong> {{ \Illuminate\Support\Str::limit($previewCaption, 120, '...') }}
                                    </div>
                                </div>
                                @break

                            @default
                                @php
                                    $prevEnum = $previewProfile->platform instanceof \App\Enums\SocialPlatform ? $previewProfile->platform : \App\Enums\SocialPlatform::tryFrom($previewProfile->platform);
                                @endphp
                                <!-- Generic / LinkedIn / X Card -->
                                <div class="post-preview-card shadow-xs">
                                    <div class="preview-header">
                                        <span class="platform-icon {{ $prevEnum?->value ?? 'facebook' }}" style="width: 32px; height: 32px;">
                                            <i class="bi {{ $prevEnum?->icon() }}"></i>
                                        </span>
                                        <div>
                                            <div class="fw-semibold text-dark small">{{ $previewProfile->name }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Preview Mode</div>
                                        </div>
                                    </div>
                                    <div class="preview-body">
                                        {{ $previewCaption ?: 'Preview text...' }}
                                    </div>
                                    @if ($firstMedia)
                                        <img src="{{ $firstMedia->public_url }}" class="preview-media">
                                    @endif
                                </div>
                        @endswitch
                    @else
                        <div class="p-4 text-center text-muted small">
                            Select at least one social profile to view live preview.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Validation Warnings Box -->
            @php $validationErrors = $this->getValidationErrorsProperty(); @endphp
            @if (!empty($validationErrors))
                <div class="alert alert-warning p-2 small mb-3">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle me-1"></i> Validation Inspector:</div>
                    <ul class="mb-0 ps-3">
                        @foreach ($validationErrors as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Scheduling & Publishing Box -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <h6 class="fw-bold small mb-3 text-dark">Publishing Options</h6>

                    <!-- Mode Select -->
                    <div class="btn-group w-100 mb-3 btn-group-sm" role="group">
                        <input type="radio" class="btn-check" wire:model.live="publishMode" value="now" id="modeNow" autocomplete="off">
                        <label class="btn btn-outline-primary" for="modeNow">Publish Now</label>

                        <input type="radio" class="btn-check" wire:model.live="publishMode" value="schedule" id="modeSchedule" autocomplete="off">
                        <label class="btn btn-outline-primary" for="modeSchedule">Schedule</label>

                        <input type="radio" class="btn-check" wire:model.live="publishMode" value="draft" id="modeDraft" autocomplete="off">
                        <label class="btn btn-outline-primary" for="modeDraft">Draft</label>
                    </div>

                    @if ($publishMode === 'schedule')
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.75rem;">Date & Time</label>
                            <div class="row g-2">
                                <div class="col-7">
                                    <input type="date" class="form-control form-control-sm" wire:model="scheduledDate">
                                </div>
                                <div class="col-5">
                                    <input type="time" class="form-control form-control-sm" wire:model="scheduledTime">
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Trigger Buttons -->
                    <div class="d-grid gap-2">
                        @if ($publishMode === 'now')
                            <button type="button" wire:click="savePost('now')" class="btn btn-primary d-flex align-items-center justify-content-center gap-2" wire:loading.attr="disabled">
                                <i class="bi bi-send-fill"></i>
                                <span>Publish to {{ count($selectedProfileIds) }} Profile(s)</span>
                            </button>
                        @elseif ($publishMode === 'schedule')
                            <button type="button" wire:click="savePost('schedule')" class="btn btn-primary d-flex align-items-center justify-content-center gap-2" wire:loading.attr="disabled">
                                <i class="bi bi-calendar-check-fill"></i>
                                <span>Schedule Post</span>
                            </button>
                        @else
                            <button type="button" wire:click="savePost('draft')" class="btn btn-secondary d-flex align-items-center justify-content-center gap-2" wire:loading.attr="disabled">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Save as Draft</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Library Asset Selector Modal -->
    @if ($showMediaModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content shadow border-0">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fs-6 fw-bold mb-0">Select Assets from Media Library</h5>
                            <span class="text-muted small">Choose photos or videos to attach to this post.</span>
                        </div>
                        <button type="button" class="btn-close" wire:click="$set('showMediaModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <input type="text" class="form-control form-control-sm bg-light w-50" wire:model.live.debounce.300ms="mediaSearch" placeholder="Search library...">
                            <span class="small text-muted">{{ count($selectedMediaAssetIds) }} asset(s) selected</span>
                        </div>

                        <div class="row g-3" style="max-height: 440px; overflow-y: auto;">
                            @forelse ($modalMediaAssets as $media)
                                @php $isMediaSelected = in_array($media->id, $selectedMediaAssetIds, true); @endphp
                                <div class="col-md-2 col-sm-4 col-6">
                                    <div wire:click="toggleMediaAsset({{ $media->id }})" class="card h-100 border p-1 cursor-pointer position-relative {{ $isMediaSelected ? 'border-primary border-2' : '' }}" style="cursor: pointer;">
                                        @if ($isMediaSelected)
                                            <span class="position-absolute top-0 end-0 m-1 badge bg-primary rounded-circle p-1">
                                                <i class="bi bi-check2 fs-6"></i>
                                            </span>
                                        @endif
                                        <div style="height: 100px; overflow: hidden;" class="rounded bg-dark d-flex align-items-center justify-content-center">
                                            @if ($media->isImage() || $media->media_type === 'gif')
                                                <img src="{{ $media->public_url }}" class="w-100 h-100" style="object-fit: cover;">
                                            @else
                                                <i class="bi bi-play-circle text-white fs-3"></i>
                                            @endif
                                        </div>
                                        <div class="text-truncate small p-1 fw-medium" style="font-size: 0.75rem;">
                                            {{ $media->file_name }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 py-5 text-center text-muted small">
                                    No assets found in Media Library. Upload assets in Media Library first!
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-primary" wire:click="$set('showMediaModal', false)">
                            Done Selecting ({{ count($selectedMediaAssetIds) }} Attached)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- AI Content Assistant Modal -->
    @if ($showAiModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header border-bottom py-3">
                        <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-stars text-warning fs-5"></i> AI Content Assistant
                        </h6>
                        <button type="button" class="btn-close" wire:click="closeAiModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Action Mode</label>
                                <select wire:model.live="aiAction" class="form-select form-select-sm">
                                    <option value="caption">Generate New Caption from Topic</option>
                                    <option value="rewrite">Rewrite & Polish Content</option>
                                    <option value="hashtags">Suggest High-Engagement Hashtags</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Tone of Voice</label>
                                <select wire:model="aiTone" class="form-select form-select-sm">
                                    <option value="engaging">Engaging & Conversational</option>
                                    <option value="professional">Professional & Authoritative</option>
                                    <option value="humorous">Humorous & Playful</option>
                                    <option value="urgent">Urgent & Promotional</option>
                                    <option value="educational">Educational & Informative</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">
                                    {{ $aiAction === 'caption' ? 'Topic, Announcement or Key Message' : 'Source Content' }}
                                </label>
                                <textarea wire:model="aiPromptTopic" class="form-control" rows="3" placeholder="e.g. Announcing our 20% discount on all bakery products this weekend..."></textarea>
                            </div>

                            <div class="col-12 text-end">
                                <button type="button" wire:click="generateAiContent" wire:loading.attr="disabled" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                                    <span wire:loading.remove wire:target="generateAiContent"><i class="bi bi-magic me-1"></i> Generate with AI</span>
                                    <span wire:loading wire:target="generateAiContent"><span class="spinner-border spinner-border-sm me-1"></span> Writing Content...</span>
                                </button>
                            </div>

                            @if (!empty($aiGeneratedResult))
                                <div class="col-12 mt-3 pt-3 border-top">
                                    <label class="form-label small fw-bold text-success d-flex align-items-center gap-1">
                                        <i class="bi bi-check-circle-fill"></i> Generated Copy Preview:
                                    </label>
                                    <div class="p-3 bg-light rounded text-dark fs-6 border" style="white-space: pre-wrap; max-height: 200px; overflow-y: auto;">
                                        {{ $aiGeneratedResult }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="closeAiModal">Close</button>
                        @if (!empty($aiGeneratedResult))
                            <button type="button" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1" wire:click="applyAiResult">
                                <i class="bi bi-check2 me-1"></i> Apply to Composer
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Custom Thumbnail Selector Modal (Media Library) -->
    @if ($showThumbnailModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle p-2 bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-image fs-5"></i>
                            </span>
                            <div>
                                <h6 class="modal-title fw-bold text-dark mb-0">Select Video Thumbnail</h6>
                                <small class="text-muted">Choose an image from your Media Library to set as custom video cover</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="$set('showThumbnailModal', false)"></button>
                    </div>
                    <div class="modal-body p-4">
                        @php
                            $availableThumbnails = \App\Models\MediaAsset::where('workspace_id', auth()->user()->current_workspace_id ?? app('current_workspace')?->id)
                                ->where('media_type', 'image')
                                ->latest()
                                ->take(18)
                                ->get();
                        @endphp

                        @if ($availableThumbnails->isNotEmpty())
                            <div class="row g-3">
                                @foreach ($availableThumbnails as $tAsset)
                                    <div class="col-md-3 col-sm-4 col-6">
                                        <div wire:click="selectThumbnailAsset({{ $selectingThumbnailForProfileId }}, '{{ $tAsset->public_url }}')" 
                                             class="border rounded-3 overflow-hidden cursor-pointer shadow-xs position-relative hover-shadow transition" style="cursor: pointer; height: 110px;">
                                            <img src="{{ $tAsset->public_url }}" class="w-100 h-100 object-fit-cover" alt="{{ $tAsset->original_name }}">
                                            <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-1 text-truncate small" style="font-size: 0.7rem;">
                                                {{ $tAsset->original_name }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-images fs-1 text-secondary opacity-50 mb-2"></i>
                                <h6 class="fw-bold text-dark">No Images Found</h6>
                                <p class="small text-muted mb-3">Upload images in Media & Docs first or use direct image upload.</p>
                                <label class="btn btn-sm btn-primary cursor-pointer">
                                    <i class="bi bi-upload me-1"></i> Upload Image Now
                                    <input type="file" wire:model="directThumbnailUpload" class="d-none" accept="image/*">
                                </label>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-sm btn-light border" wire:click="$set('showThumbnailModal', false)">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

