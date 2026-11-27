<div>
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-images text-primary me-2"></i>Media Library
            </h4>
            <p class="text-muted small mb-0">
                Upload and organize your social media photos, videos, and branding assets with Cloudflare R2 / S3 storage.
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button wire:click="$set('showFolderModal', true)" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-folder-plus"></i>
                <span>New Folder</span>
            </button>
            <label class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 mb-0 cursor-pointer">
                <i class="bi bi-cloud-arrow-up"></i>
                <span>Upload Assets</span>
                <input type="file" wire:model="uploads" class="d-none" multiple accept="image/*,video/*,application/pdf">
            </label>
        </div>
    </div>

    <!-- Upload Progress Indicator -->
 

    <!-- Breadcrumb & Navigation -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center justify-content-between">
                <div class="col-md-6 d-flex flex-wrap align-items-center gap-2">
                    <button wire:click="selectFolder(null)" class="btn btn-sm {{ is_null($currentFolderId) ? 'btn-primary' : 'btn-light border' }}">
                        <i class="bi bi-grid-fill me-1"></i> Root / All
                    </button>
                    @if ($currentFolder)
                        <span class="text-muted small">/</span>
                        <span class="badge bg-light text-dark border px-2 py-1 fs-6 text-truncate" style="max-width: 180px;">
                            <i class="bi bi-folder-fill text-warning me-1"></i> {{ $currentFolder->name }}
                        </span>
                    @endif
                </div>
                <div class="col-md-6 d-flex flex-wrap flex-sm-nowrap align-items-center gap-2">
                    <input type="text" class="form-control form-control-sm bg-light flex-grow-1" wire:model.live.debounce.300ms="search" placeholder="Search files...">
                    <select class="form-select form-select-sm bg-light w-100 w-sm-auto" wire:model.live="typeFilter">
                        <option value="all">All Formats</option>
                        <option value="image">Images</option>
                        <option value="video">Videos</option>
                        <option value="gif">GIFs</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Subfolders Grid (if any) -->
    @if ($folders->count() > 0)
        <div class="row g-3 mb-4">
            @foreach ($folders as $folder)
                <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                    <div wire:click="selectFolder({{ $folder->id }})" class="card border shadow-sm p-3 cursor-pointer text-decoration-none hover-shadow" style="cursor: pointer;">
                        <div class="d-flex align-items-center justify-content-between gap-2 min-w-0">
                            <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
                                <i class="bi bi-folder-fill text-warning fs-4 flex-shrink-0"></i>
                                <div class="min-w-0 flex-grow-1">
                                    <h6 class="fw-bold mb-0 text-dark small text-truncate">{{ $folder->name }}</h6>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $folder->assets_count }} items</span>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted small flex-shrink-0"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Media Assets Grid -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            @if ($assets->count() > 0)
                <div class="row g-2 g-sm-3">
                    @foreach ($assets as $asset)
                        <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                            <div class="card h-100 border overflow-hidden shadow-xs post-preview-card">
                                <!-- Media Thumbnail Preview -->
                                <div class="position-relative bg-dark d-flex align-items-center justify-content-center media-thumb-box">
                                    @if ($asset->isImage() || $asset->media_type === 'gif')
                                        <img src="{{ $asset->public_url }}" alt="{{ $asset->file_name }}" class="w-100 h-100" style="object-fit: cover;" onerror="this.onerror=null; this.classList.add('d-none'); if (this.nextElementSibling) this.nextElementSibling.classList.remove('d-none');">
                                        <div class="d-none text-center p-3 text-white-50">
                                            <i class="bi bi-image fs-1 d-block mb-1"></i>
                                            <span style="font-size: 0.75rem;">Preview unavailable</span>
                                        </div>
                                    @elseif ($asset->isVideo())
                                        <video src="{{ $asset->public_url }}" class="w-100 h-100" style="object-fit: cover;"></video>
                                        <div class="position-absolute text-white fs-2" style="background: rgba(0,0,0,0.4); border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-play-fill"></i>
                                        </div>
                                    @else
                                        <i class="bi bi-file-earmark-text text-secondary fs-1"></i>
                                    @endif

                                    <!-- Aspect Ratio Badge -->
                                    @if ($asset->aspect_ratio)
                                        <span class="position-absolute top-0 end-0 m-2 badge bg-dark bg-opacity-75 small">
                                            {{ $asset->aspect_ratio }}
                                        </span>
                                    @endif
                                </div>

                                <div class="card-body p-2">
                                    <div class="fw-semibold text-dark text-truncate small" title="{{ $asset->file_name }}">
                                        {{ $asset->file_name }}
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between text-muted mt-1" style="font-size: 0.75rem;">
                                        <span>{{ $asset->humanFileSize() }}</span>
                                        @if ($asset->width && $asset->height)
                                            <span>{{ $asset->width }}x{{ $asset->height }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-footer bg-light p-2 d-flex justify-content-between align-items-center border-top">
                                    <button wire:click="openDetailModal({{ $asset->id }})" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                        <i class="bi bi-info-circle me-1"></i> Details
                                    </button>
                                    <button wire:click="deleteAsset({{ $asset->id }})" wire:confirm="Are you sure you want to delete this media file?" class="btn btn-sm btn-light text-danger py-0 px-2" style="font-size: 0.75rem;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-2 border-top">
                    {{ $assets->links() }}
                </div>
            @else
                <x-empty-state 
                    icon="bi-cloud-arrow-up"
                    title="No media in this folder"
                    description="Drag & drop or upload photos and videos to start creating social media posts."
                />
            @endif
        </div>
    </div>

    <!-- Create Folder Modal -->
    @if ($showFolderModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm">
                <div class="modal-content shadow border-0">
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-bold">Create Folder</h5>
                        <button type="button" class="btn-close" wire:click="$set('showFolderModal', false)"></button>
                    </div>
                    <form wire:submit="createFolder">
                        <div class="modal-body">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Folder Name</label>
                                <input type="text" class="form-control @error('newFolderName') is-invalid @enderror" wire:model="newFolderName" placeholder="e.g. Summer Campaign, Menu Photos" required autofocus>
                                @error('newFolderName') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-sm btn-secondary" wire:click="$set('showFolderModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Create Folder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Asset Inspector Details Modal -->
    @if ($showDetailModal && $selectedAsset)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content shadow border-0">
                    <div class="modal-header gap-2">
                        <h5 class="modal-title fs-6 fw-bold text-truncate min-w-0">
                            {{ $selectedAsset->file_name }}
                        </h5>
                        <button type="button" class="btn-close flex-shrink-0" wire:click="$set('showDetailModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <!-- Preview Column -->
                            <div class="col-md-6 text-center bg-light p-3 rounded-3 d-flex align-items-center justify-content-center" style="min-height: 250px;">
                                @if ($selectedAsset->isImage() || $selectedAsset->media_type === 'gif')
                                    <img src="{{ $selectedAsset->public_url }}" alt="{{ $selectedAsset->file_name }}" class="img-fluid rounded shadow-xs" style="max-height: 300px; object-fit: contain;" onerror="this.onerror=null; this.classList.add('d-none'); if (this.nextElementSibling) this.nextElementSibling.classList.remove('d-none');">
                                    <div class="d-none text-center p-3 text-muted">
                                        <i class="bi bi-image fs-1 d-block mb-1 text-secondary"></i>
                                        <span class="small">Image preview not available</span>
                                    </div>
                                @elseif ($selectedAsset->isVideo())
                                    <video src="{{ $selectedAsset->public_url }}" controls class="w-100 rounded" style="max-height: 300px;"></video>
                                @endif
                            </div>

                            <!-- Details Column -->
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">File Information</h6>
                                <ul class="list-unstyled small text-secondary mb-3">
                                    <li class="mb-2"><strong class="text-dark">Size:</strong> {{ $selectedAsset->humanFileSize() }}</li>
                                    <li class="mb-2"><strong class="text-dark">MIME Type:</strong> {{ $selectedAsset->mime_type }}</li>
                                    @if ($selectedAsset->width && $selectedAsset->height)
                                        <li class="mb-2"><strong class="text-dark">Dimensions:</strong> {{ $selectedAsset->width }} x {{ $selectedAsset->height }} px</li>
                                        <li class="mb-2"><strong class="text-dark">Aspect Ratio:</strong> {{ $selectedAsset->aspect_ratio }}</li>
                                    @endif
                                    <li class="mb-2"><strong class="text-dark">Storage Disk:</strong> <span class="badge bg-light text-dark border">{{ $selectedAsset->disk }}</span></li>
                                    <li class="mb-2"><strong class="text-dark">Uploaded:</strong> {{ $selectedAsset->created_at->format('M d, Y H:i') }}</li>
                                </ul>

                                <!-- Public URL -->
                                <label class="form-label small fw-semibold text-dark mb-1">Public CDN URL:</label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="text" class="form-control" value="{{ $selectedAsset->public_url }}" readonly>
                                    <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ $selectedAsset->public_url }}')">
                                        Copy
                                    </button>
                                </div>

                                <!-- Tags Section -->
                                <label class="form-label small fw-semibold text-dark mb-1">Asset Tags:</label>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @forelse ($selectedAsset->tags as $tag)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-1">
                                            #{{ $tag->name }}
                                            <i wire:click="removeTagFromAsset({{ $tag->id }})" class="bi bi-x cursor-pointer" style="cursor: pointer;"></i>
                                        </span>
                                    @empty
                                        <span class="text-muted small fst-italic">No tags added</span>
                                    @endforelse
                                </div>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" wire:model="newTagName" placeholder="Add tag (e.g. food, summer)..." wire:keydown.enter="addTagToAsset">
                                    <button wire:click="addTagToAsset" class="btn btn-outline-primary">Add Tag</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 justify-content-between">
                        <button wire:click="deleteAsset({{ $selectedAsset->id }})" wire:confirm="Are you sure you want to delete this asset?" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash me-1"></i> Delete Asset
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" wire:click="$set('showDetailModal', false)">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

