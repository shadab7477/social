<div class="mx-auto" style="max-width: 1080px;">
    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-0">
                <i class="bi bi-pencil-square text-primary me-2"></i>Create Post
            </h4>
            <p class="text-muted small mb-0">Simple 4-step workflow to upload, configure, preview, and publish.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" wire:click="openAiModal('base')" class="btn btn-sm btn-outline-primary rounded-pill px-3 d-inline-flex align-items-center gap-1">
                <i class="bi bi-stars text-warning"></i> AI Assistant
            </button>
            <a href="{{ route('posts.index') }}" class="btn btn-sm btn-white bg-white border rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Posts
            </a>
        </div>
    </div>

    <!-- Step Progress Bar -->
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body p-2 p-md-3">
            <div class="row g-2 text-center">
                @php
                    $steps = [
                        1 => ['label' => '1. Accounts', 'icon' => 'bi-people'],
                        2 => ['label' => '2. Media & Caption', 'icon' => 'bi-images'],
                        3 => ['label' => '3. Platform Settings', 'icon' => 'bi-sliders'],
                        4 => ['label' => '4. Preview & Publish', 'icon' => 'bi-send-check'],
                    ];
                @endphp
                @foreach ($steps as $sNum => $sInfo)
                    @php
                        $isActive = $currentStep === $sNum;
                        $isDone = $currentStep > $sNum;
                    @endphp
                    <div class="col-6 col-md-3">
                        <button type="button" wire:click="goToStep({{ $sNum }})"
                            class="btn w-100 rounded-pill py-2 px-3 d-flex align-items-center justify-content-center gap-2 fw-semibold transition {{ $isActive ? 'btn-dark shadow-xs' : ($isDone ? 'btn-light border text-success' : 'btn-light border text-muted') }}"
                            style="font-size: 0.82rem;">
                            <i class="bi {{ $isDone ? 'bi-check-circle-fill text-success' : $sInfo['icon'] }}"></i>
                            <span>{{ $sInfo['label'] }}</span>
                            @if ($sNum === 1 && count($selectedProfileIds) > 0)
                                <span class="badge {{ $isActive ? 'bg-white text-dark' : 'bg-dark text-white' }} rounded-pill">{{ count($selectedProfileIds) }}</span>
                            @endif
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 rounded-3 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- ========================================================== -->
    <!-- STEP 1: SELECT PUBLISHING ACCOUNTS                         -->
    <!-- ========================================================== -->
    @if ($currentStep === 1)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Step 1: Where do you want to publish?</h6>
                    <small class="text-muted">Select one or more social accounts below.</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm bg-light rounded-pill px-3" wire:model.live="selectedClientId" style="min-width: 180px;">
                        <option value="">All Clients / Agency</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="selectAllProfiles" class="btn btn-xs btn-outline-primary rounded-pill px-2">All</button>
                    <button type="button" wire:click="deselectAllProfiles" class="btn btn-xs btn-outline-secondary rounded-pill px-2">Clear</button>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">
                    @forelse ($availableProfiles as $profile)
                        @php
                            $isSelected = in_array($profile->id, $selectedProfileIds, true);
                            $pEnum = $profile->platform instanceof \App\Enums\SocialPlatform ? $profile->platform : \App\Enums\SocialPlatform::tryFrom($profile->platform);
                        @endphp
                        <div class="col-md-6 col-lg-4">
                            <div wire:click="toggleProfile({{ $profile->id }})"
                                class="p-3 border rounded-4 cursor-pointer d-flex align-items-center gap-3 transition h-100 {{ $isSelected ? 'border-dark bg-light shadow-xs' : 'bg-white' }}"
                                style="cursor: pointer;">
                                <input class="form-check-input flex-shrink-0 m-0" type="checkbox" {{ $isSelected ? 'checked' : '' }} style="width: 1.15rem; height: 1.15rem;">
                                <span class="platform-icon {{ $pEnum?->value ?? 'facebook' }} flex-shrink-0" style="width: 38px; height: 38px; font-size: 1.05rem;">
                                    <i class="bi {{ $pEnum?->icon() }}"></i>
                                </span>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-bold text-dark small text-truncate">{{ $profile->name }}</div>
                                    <div class="text-muted text-truncate" style="font-size: 0.72rem;">
                                        {{ $pEnum?->label() }}
                                        @if ($profile->client) &bull; {{ $profile->client->name }} @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 py-5 text-center text-muted">
                            <i class="bi bi-link-45deg fs-1 d-block mb-2 opacity-50"></i>
                            <h6 class="fw-bold text-dark">No Social Accounts Connected</h6>
                            <p class="small mb-3">Connect your Facebook, Instagram, YouTube, or LinkedIn account first.</p>
                            <a href="{{ route('social-accounts.index') }}" class="btn btn-sm btn-primary rounded-pill px-4">Connect Account</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center">
                <span class="small text-muted fw-medium">{{ count($selectedProfileIds) }} account(s) selected</span>
                <button type="button" wire:click="nextStep" class="btn btn-dark rounded-pill px-4 fw-semibold">
                    Next: Media &amp; Caption <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

    <!-- ========================================================== -->
    <!-- STEP 2: UPLOAD MEDIA & WRITE CAPTION                       -->
    <!-- ========================================================== -->
    @elseif ($currentStep === 2)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">Step 2: Add Media &amp; Write Caption</h6>
                <small class="text-muted">Upload your photo/video and write your caption or video description.</small>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Left: Media Upload -->
                    <div class="col-lg-5">
                        <label class="form-label small fw-bold text-dark mb-2">Photos or Videos</label>
                        <div class="border border-dashed rounded-4 p-4 text-center bg-light mb-3">
                            <i class="bi bi-cloud-arrow-up fs-2 text-primary d-block mb-2"></i>
                            <div class="fw-semibold small text-dark mb-1">Upload from device or pick from library</div>
                            <div class="text-muted mb-3" style="font-size: 0.72rem;">Supports JPG, PNG, MP4, MOV (max 100MB)</div>

                            <div class="d-flex justify-content-center gap-2">
                                <label class="btn btn-sm btn-dark rounded-pill px-3 mb-0 cursor-pointer">
                                    <span wire:loading.remove wire:target="directMediaUpload"><i class="bi bi-upload me-1"></i> Upload File</span>
                                    <span wire:loading wire:target="directMediaUpload"><span class="spinner-border spinner-border-sm me-1"></span> Uploading...</span>
                                    <input type="file" wire:model="directMediaUpload" class="d-none" accept="image/*,video/mp4,video/quicktime">
                                </label>
                                <button type="button" wire:click="openMediaModal" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bi bi-images me-1"></i> Library
                                </button>
                            </div>
                        </div>

                        @if ($selectedMediaAssets->count() > 0)
                            <div class="small fw-semibold text-dark mb-2">Attached Media ({{ $selectedMediaAssets->count() }})</div>
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach ($selectedMediaAssets as $media)
                                    <div class="position-relative border rounded-3 overflow-hidden shadow-xs" style="width: 78px; height: 78px;">
                                        @if ($media->isImage() || $media->media_type === 'gif')
                                            <img src="{{ $media->public_url }}" class="w-100 h-100" style="object-fit: cover;">
                                        @else
                                            <div class="w-100 h-100 bg-dark text-white d-flex flex-column align-items-center justify-content-center">
                                                <i class="bi bi-play-circle-fill fs-4 text-danger"></i>
                                                <span style="font-size: 0.6rem;">VIDEO</span>
                                            </div>
                                        @endif
                                        <button type="button" wire:click="removeMediaAsset({{ $media->id }})" class="btn btn-danger position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center rounded-circle" style="width: 20px; height: 20px; font-size: 0.7rem;">
                                            &times;
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Right: Caption & Link -->
                    <div class="col-lg-7">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-bold text-dark mb-0">Caption / Description</label>
                            <button type="button" wire:click="openAiModal('base')" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                <i class="bi bi-stars text-warning"></i> Write with AI
                            </button>
                        </div>

                        <textarea class="form-control rounded-3 p-3 mb-3" wire:model.live.debounce.250ms="baseCaption" rows="6" placeholder="What would you like to share? For YouTube, this becomes your video description..."></textarea>

                        <div>
                            <label class="form-label small fw-semibold text-muted mb-1">Destination Link (Optional)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                                <input type="url" class="form-control" wire:model.live="destinationLink" placeholder="https://yourwebsite.com/promo">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center">
                <button type="button" wire:click="prevStep" class="btn btn-light border rounded-pill px-4 fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </button>
                <button type="button" wire:click="nextStep" class="btn btn-dark rounded-pill px-4 fw-semibold">
                    Next: Platform Settings <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

    <!-- ========================================================== -->
    <!-- STEP 3: PLATFORM-SPECIFIC SETTINGS                         -->
    <!-- ========================================================== -->
    @elseif ($currentStep === 3)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">Step 3: Configure Each Platform</h6>
                <small class="text-muted">Choose video format, thumbnail, Reel/Story mode, or customize caption per network.</small>
            </div>

            <div class="card-body p-4">
                <div class="d-flex flex-column gap-4">
                    @foreach ($availableProfiles as $profile)
                        @if (in_array($profile->id, $selectedProfileIds, true))
                            @php
                                $pVal = $profile->platform instanceof \App\Enums\SocialPlatform ? $profile->platform->value : (string)$profile->platform;
                                $pEnum = $profile->platform instanceof \App\Enums\SocialPlatform ? $profile->platform : \App\Enums\SocialPlatform::tryFrom($profile->platform);
                                $settings = $platformSettings[$profile->id] ?? [];
                            @endphp
                            <div class="p-4 border rounded-4 bg-light" x-data="{ customCap: {{ !empty($customizedFlags[$profile->id]) ? 'true' : 'false' }}, showMoreYt: false }">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="platform-icon {{ $pEnum?->value ?? 'facebook' }}" style="width: 34px; height: 34px;">
                                            <i class="bi {{ $pEnum?->icon() }}"></i>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $profile->name }}</div>
                                            <div class="small text-muted" style="font-size: 0.72rem;">{{ $pEnum?->label() }}</div>
                                        </div>
                                    </div>
                                    <button type="button" @click="customCap = !customCap" wire:click="setTab('{{ $profile->id }}')" class="btn btn-xs btn-white bg-white border rounded-pill px-3 py-1 text-dark" style="font-size: 0.74rem;">
                                        <i class="bi bi-pencil me-1"></i> Custom Caption
                                    </button>
                                </div>

                                <!-- Optional Custom Caption Override -->
                                <div x-show="customCap" x-cloak class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Custom Caption for {{ $profile->name }}</label>
                                    <textarea class="form-control bg-white" wire:model.live.debounce.250ms="customCaptions.{{ $profile->id }}" rows="3" placeholder="Leave blank to use your main caption from Step 2..."></textarea>
                                </div>

                                @if ($pVal === 'youtube')
                                    <div class="row g-3">
                                        <!-- Format -->
                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-dark mb-1">Upload Type</label>
                                            <div class="d-flex gap-2">
                                                <button type="button" wire:click="$set('platformSettings.{{ $profile->id }}.post_type', 'video')"
                                                    class="btn btn-sm flex-grow-1 rounded-pill py-2 {{ ($settings['post_type'] ?? 'video') === 'video' ? 'btn-danger' : 'btn-white bg-white border text-dark' }}">
                                                    <i class="bi bi-film me-1"></i> Standard Video (16:9)
                                                </button>
                                                <button type="button" wire:click="$set('platformSettings.{{ $profile->id }}.post_type', 'shorts')"
                                                    class="btn btn-sm flex-grow-1 rounded-pill py-2 {{ ($settings['post_type'] ?? '') === 'shorts' ? 'btn-danger' : 'btn-white bg-white border text-dark' }}">
                                                    <i class="bi bi-lightning-charge-fill me-1"></i> YouTube Shorts (9:16)
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Video Title -->
                                        <div class="col-md-8">
                                            <label class="form-label small fw-bold text-dark mb-1">Video Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control bg-white" wire:model="platformSettings.{{ $profile->id }}.title" maxlength="100" placeholder="Enter compelling video title...">
                                        </div>

                                        <!-- Privacy -->
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-dark mb-1">Visibility</label>
                                            <select class="form-select bg-white" wire:model="platformSettings.{{ $profile->id }}.privacy_status">
                                                <option value="public">Public</option>
                                                <option value="unlisted">Unlisted</option>
                                                <option value="private">Private</option>
                                            </select>
                                        </div>

                                        <!-- Custom Thumbnail -->
                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-dark mb-1">Custom Video Thumbnail (Optional)</label>
                                            @if (!empty($settings['thumbnail_url']))
                                                <div class="d-flex align-items-center gap-3 p-2 bg-white border rounded-3">
                                                    <img src="{{ $settings['thumbnail_url'] }}" class="rounded" style="width: 96px; height: 54px; object-fit: cover;">
                                                    <div class="flex-grow-1 small text-success fw-semibold">Thumbnail Attached</div>
                                                    <button type="button" wire:click="removeThumbnail({{ $profile->id }})" class="btn btn-sm btn-outline-danger rounded-pill px-3">Remove</button>
                                                </div>
                                            @else
                                                <div class="d-flex flex-wrap gap-2">
                                                    <label class="btn btn-sm btn-dark rounded-pill px-3 mb-0 cursor-pointer">
                                                        <span wire:loading.remove wire:target="directThumbnailUploads.{{ $profile->id }}"><i class="bi bi-upload me-1"></i> Upload Thumbnail</span>
                                                        <span wire:loading wire:target="directThumbnailUploads.{{ $profile->id }}"><span class="spinner-border spinner-border-sm me-1"></span> Uploading...</span>
                                                        <input type="file" wire:model="directThumbnailUploads.{{ $profile->id }}" class="d-none" accept="image/*">
                                                    </label>
                                                    <button type="button" wire:click="openThumbnailModal({{ $profile->id }})" class="btn btn-sm btn-white bg-white border rounded-pill px-3">
                                                        <i class="bi bi-images me-1"></i> Choose from Library
                                                    </button>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Advanced Toggle -->
                                        <div class="col-12">
                                            <button type="button" @click="showMoreYt = !showMoreYt" class="btn btn-xs btn-link text-decoration-none p-0 text-muted">
                                                <span x-text="showMoreYt ? '- Hide Tags & Category' : '+ Add Tags, Category & Kids Setting'"></span>
                                            </button>
                                            <div x-show="showMoreYt" x-cloak class="row g-2 mt-2">
                                                <div class="col-md-5">
                                                    <label class="form-label small fw-semibold mb-1">Category</label>
                                                    <select class="form-select form-select-sm bg-white" wire:model="platformSettings.{{ $profile->id }}.category_id">
                                                        <option value="22">People &amp; Blogs</option>
                                                        <option value="28">Science &amp; Technology</option>
                                                        <option value="27">Education</option>
                                                        <option value="24">Entertainment</option>
                                                        <option value="17">Sports</option>
                                                        <option value="26">Howto &amp; Style</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-7">
                                                    <label class="form-label small fw-semibold mb-1">Video Tags</label>
                                                    <input type="text" class="form-control form-control-sm bg-white" wire:model="platformSettings.{{ $profile->id }}.tags" placeholder="tutorial, tech, reviews">
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="ytKids{{ $profile->id }}" wire:model="platformSettings.{{ $profile->id }}.made_for_kids">
                                                        <label class="form-check-label small" for="ytKids{{ $profile->id }}">Made for kids (COPPA)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($pVal === 'facebook')
                                    <label class="form-label small fw-bold text-dark mb-2">Choose Facebook Format</label>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <button type="button" wire:click="$set('platformSettings.{{ $profile->id }}.post_type', 'feed')"
                                                class="btn w-100 py-2 rounded-3 {{ ($settings['post_type'] ?? 'feed') === 'feed' ? 'btn-primary' : 'btn-white bg-white border text-dark' }}">
                                                <i class="bi bi-card-text d-block fs-5 mb-1"></i>
                                                <span class="small fw-semibold">Page Post</span>
                                            </button>
                                        </div>
                                        <div class="col-4">
                                            <button type="button" wire:click="$set('platformSettings.{{ $profile->id }}.post_type', 'reel')"
                                                class="btn w-100 py-2 rounded-3 {{ ($settings['post_type'] ?? '') === 'reel' ? 'btn-primary' : 'btn-white bg-white border text-dark' }}">
                                                <i class="bi bi-play-btn d-block fs-5 mb-1"></i>
                                                <span class="small fw-semibold">Facebook Reel</span>
                                            </button>
                                        </div>
                                        <div class="col-4">
                                            <button type="button" wire:click="$set('platformSettings.{{ $profile->id }}.post_type', 'story')"
                                                class="btn w-100 py-2 rounded-3 {{ ($settings['post_type'] ?? '') === 'story' ? 'btn-primary' : 'btn-white bg-white border text-dark' }}">
                                                <i class="bi bi-circle d-block fs-5 mb-1"></i>
                                                <span class="small fw-semibold">Story</span>
                                            </button>
                                        </div>
                                    </div>
                                @elseif ($pVal === 'instagram')
                                    <label class="form-label small fw-bold text-dark mb-2">Choose Instagram Format</label>
                                    <div class="row g-2 mb-2">
                                        <div class="col-4">
                                            <button type="button" wire:click="$set('platformSettings.{{ $profile->id }}.post_type', 'feed')"
                                                class="btn w-100 py-2 rounded-3 {{ ($settings['post_type'] ?? 'feed') === 'feed' ? 'btn-dark' : 'btn-white bg-white border text-dark' }}">
                                                <i class="bi bi-grid-3x3 d-block fs-5 mb-1"></i>
                                                <span class="small fw-semibold">Feed Post</span>
                                            </button>
                                        </div>
                                        <div class="col-4">
                                            <button type="button" wire:click="$set('platformSettings.{{ $profile->id }}.post_type', 'reel')"
                                                class="btn w-100 py-2 rounded-3 {{ ($settings['post_type'] ?? '') === 'reel' ? 'btn-dark' : 'btn-white bg-white border text-dark' }}">
                                                <i class="bi bi-play-btn-fill d-block fs-5 mb-1"></i>
                                                <span class="small fw-semibold">Reel</span>
                                            </button>
                                        </div>
                                        <div class="col-4">
                                            <button type="button" wire:click="$set('platformSettings.{{ $profile->id }}.post_type', 'story')"
                                                class="btn w-100 py-2 rounded-3 {{ ($settings['post_type'] ?? '') === 'story' ? 'btn-dark' : 'btn-white bg-white border text-dark' }}">
                                                <i class="bi bi-circle d-block fs-5 mb-1"></i>
                                                <span class="small fw-semibold">Story</span>
                                            </button>
                                        </div>
                                    </div>
                                    @if(($settings['post_type'] ?? 'feed') === 'reel')
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="igShareFeed{{ $profile->id }}" wire:model="platformSettings.{{ $profile->id }}.share_to_feed" checked>
                                            <label class="form-check-label small" for="igShareFeed{{ $profile->id }}">Also share Reel to main Feed</label>
                                        </div>
                                    @endif
                                @else
                                    <div class="small text-muted">
                                        <i class="bi bi-check-circle-fill text-success me-1"></i> Configured and ready to publish to {{ $pEnum?->label() }}.
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="card-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center">
                <button type="button" wire:click="prevStep" class="btn btn-light border rounded-pill px-4 fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </button>
                <button type="button" wire:click="nextStep" class="btn btn-dark rounded-pill px-4 fw-semibold">
                    Next: Preview &amp; Publish <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

    <!-- ========================================================== -->
    <!-- STEP 4: LIVE PREVIEW & PUBLISH                             -->
    <!-- ========================================================== -->
    @else
        <div class="row g-4">
            <!-- Left: Live Network Preview -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <span class="fw-bold text-dark small"><i class="bi bi-eye text-primary me-1"></i> Live Preview</span>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($availableProfiles as $profile)
                                @if (in_array($profile->id, $selectedProfileIds, true))
                                    <button type="button" wire:click="setTab('{{ $profile->id }}')"
                                        class="btn btn-xs rounded-pill px-2 py-1 {{ ($previewProfile?->id === $profile->id) ? 'btn-dark' : 'btn-light border text-muted' }}" style="font-size: 0.72rem;">
                                        {{ \Illuminate\Support\Str::limit($profile->name, 16) }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if ($previewProfile)
                            @php
                                $previewCaption = $this->getEffectiveCaption($previewProfile);
                                $firstMedia = $selectedMediaAssets->first();
                                $previewPlatformValue = $previewProfile->platform instanceof \App\Enums\SocialPlatform ? $previewProfile->platform->value : (string)$previewProfile->platform;
                            @endphp

                            @switch($previewPlatformValue)
                                @case('youtube')
                                    @php
                                        $ytSettings = $platformSettings[$previewProfile->id] ?? [];
                                        $isShorts = ($ytSettings['post_type'] ?? 'video') === 'shorts';
                                        $ytTitle = !empty($ytSettings['title']) ? $ytSettings['title'] : ($previewCaption ? \Illuminate\Support\Str::limit($previewCaption, 60) : 'Video Title');
                                        $customThumb = $ytSettings['thumbnail_url'] ?? null;
                                    @endphp
                                    <div class="post-preview-card shadow-xs mx-auto" style="max-width: 440px;">
                                        <div class="position-relative bg-dark text-white rounded-top overflow-hidden d-flex align-items-center justify-content-center" style="aspect-ratio: {{ $isShorts ? '9/16' : '16/9' }}; max-height: 300px;">
                                            @if ($customThumb)
                                                <img src="{{ $customThumb }}" class="w-100 h-100" style="object-fit: cover;">
                                            @elseif ($firstMedia && ($firstMedia->isImage() || $firstMedia->media_type === 'gif'))
                                                <img src="{{ $firstMedia->public_url }}" class="w-100 h-100" style="object-fit: cover;">
                                            @elseif ($firstMedia)
                                                <video src="{{ $firstMedia->public_url }}" class="w-100 h-100" style="object-fit: cover;" muted></video>
                                            @else
                                                <div class="text-center p-3">
                                                    <i class="bi bi-youtube fs-1 text-danger d-block mb-1"></i>
                                                    <small class="text-white-50">Attach a Video (MP4/MOV)</small>
                                                </div>
                                            @endif
                                            @if ($isShorts)
                                                <span class="position-absolute top-0 end-0 m-2 badge bg-danger">SHORTS</span>
                                            @endif
                                        </div>
                                        <div class="p-3 bg-white">
                                            <h6 class="fw-bold text-dark small mb-1">{{ $ytTitle }}</h6>
                                            <div class="text-muted" style="font-size: 0.72rem;">{{ $previewProfile->name }} &bull; {{ ucfirst($ytSettings['privacy_status'] ?? 'public') }}</div>
                                            @if ($previewCaption)
                                                <div class="mt-2 text-muted small" style="font-size: 0.78rem;">{{ \Illuminate\Support\Str::limit($previewCaption, 120) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    @break

                                @case('facebook')
                                    <div class="post-preview-card shadow-xs mx-auto" style="max-width: 440px;">
                                        <div class="preview-header">
                                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                                {{ strtoupper(substr($previewProfile->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark small">{{ $previewProfile->name }}</div>
                                                <div class="text-muted" style="font-size: 0.7rem;">{{ ucfirst($platformSettings[$previewProfile->id]['post_type'] ?? 'feed') }} &bull; Just now</div>
                                            </div>
                                        </div>
                                        <div class="preview-body">{{ $previewCaption ?: 'Preview text...' }}</div>
                                        @if ($firstMedia)
                                            @if ($firstMedia->isImage() || $firstMedia->media_type === 'gif')
                                                <img src="{{ $firstMedia->public_url }}" class="preview-media">
                                            @else
                                                <video src="{{ $firstMedia->public_url }}" class="preview-media" controls muted></video>
                                            @endif
                                        @endif
                                    </div>
                                    @break

                                @default
                                    @php $prevEnum = $previewProfile->platform instanceof \App\Enums\SocialPlatform ? $previewProfile->platform : \App\Enums\SocialPlatform::tryFrom($previewProfile->platform); @endphp
                                    <div class="post-preview-card shadow-xs mx-auto" style="max-width: 440px;">
                                        <div class="preview-header">
                                            <span class="platform-icon {{ $prevEnum?->value ?? 'linkedin' }}" style="width: 32px; height: 32px;">
                                                <i class="bi {{ $prevEnum?->icon() }}"></i>
                                            </span>
                                            <div>
                                                <div class="fw-semibold text-dark small">{{ $previewProfile->name }}</div>
                                                <div class="text-muted" style="font-size: 0.7rem;">{{ $prevEnum?->label() }}</div>
                                            </div>
                                        </div>
                                        <div class="preview-body">{{ $previewCaption ?: 'Preview text...' }}</div>
                                        @if ($firstMedia && $firstMedia->isImage())
                                            <img src="{{ $firstMedia->public_url }}" class="preview-media">
                                        @elseif ($firstMedia)
                                            <video src="{{ $firstMedia->public_url }}" class="preview-media" controls muted></video>
                                        @endif
                                    </div>
                            @endswitch
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right: Final Checklist & Publish -->
            <div class="col-lg-5">
                @php $validationErrors = $this->getValidationErrorsProperty(); @endphp
                @if (!empty($validationErrors))
                    <div class="alert alert-warning rounded-4 p-3 small mb-3">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Before you publish:</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($validationErrors as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="alert alert-success rounded-4 p-3 small mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div>Everything looks good and ready to publish to <strong>{{ count($selectedProfileIds) }} account(s)</strong>!</div>
                    </div>
                @endif

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 text-dark">Publishing Options</h6>

                        <div class="btn-group w-100 mb-3" role="group">
                            <input type="radio" class="btn-check" wire:model.live="publishMode" value="now" id="modeNow" autocomplete="off">
                            <label class="btn btn-outline-dark" for="modeNow">Publish Now</label>

                            <input type="radio" class="btn-check" wire:model.live="publishMode" value="schedule" id="modeSchedule" autocomplete="off">
                            <label class="btn btn-outline-dark" for="modeSchedule">Schedule</label>

                            <input type="radio" class="btn-check" wire:model.live="publishMode" value="draft" id="modeDraft" autocomplete="off">
                            <label class="btn btn-outline-dark" for="modeDraft">Draft</label>
                        </div>

                        @if ($publishMode === 'schedule')
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Date &amp; Time</label>
                                <div class="row g-2">
                                    <div class="col-7">
                                        <input type="date" class="form-control" wire:model="scheduledDate">
                                    </div>
                                    <div class="col-5">
                                        <input type="time" class="form-control" wire:model="scheduledTime">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="d-grid gap-2 mt-4">
                            @if ($publishMode === 'now')
                                <button type="button" wire:click="savePost('now')" class="btn btn-primary rounded-pill py-2 fw-semibold" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="savePost"><i class="bi bi-send-fill me-1"></i> Publish Now to {{ count($selectedProfileIds) }} Account(s)</span>
                                    <span wire:loading wire:target="savePost"><span class="spinner-border spinner-border-sm me-1"></span> Publishing Now... Please wait</span>
                                </button>
                            @elseif ($publishMode === 'schedule')
                                <button type="button" wire:click="savePost('schedule')" class="btn btn-primary rounded-pill py-2 fw-semibold" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="savePost"><i class="bi bi-calendar-check-fill me-1"></i> Schedule Post</span>
                                    <span wire:loading wire:target="savePost"><span class="spinner-border spinner-border-sm me-1"></span> Scheduling...</span>
                                </button>
                            @else
                                <button type="button" wire:click="savePost('draft')" class="btn btn-secondary rounded-pill py-2 fw-semibold" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="savePost"><i class="bi bi-file-earmark-text me-1"></i> Save as Draft</span>
                                    <span wire:loading wire:target="savePost"><span class="spinner-border spinner-border-sm me-1"></span> Saving...</span>
                                </button>
                            @endif

                            <button type="button" wire:click="prevStep" class="btn btn-light border rounded-pill py-2 fw-semibold mt-1">
                                <i class="bi bi-arrow-left me-1"></i> Back to Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                                    <option value="rewrite">Rewrite &amp; Polish Content</option>
                                    <option value="hashtags">Suggest High-Engagement Hashtags</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Tone of Voice</label>
                                <select wire:model="aiTone" class="form-select form-select-sm">
                                    <option value="engaging">Engaging &amp; Conversational</option>
                                    <option value="professional">Professional &amp; Authoritative</option>
                                    <option value="humorous">Humorous &amp; Playful</option>
                                    <option value="urgent">Urgent &amp; Promotional</option>
                                    <option value="educational">Educational &amp; Informative</option>
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
                                <p class="small text-muted mb-3">Upload images in Media &amp; Docs first or use direct image upload.</p>
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
