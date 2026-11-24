<?php

namespace App\Livewire\MediaLibrary;

use App\Models\MediaAsset;
use App\Models\MediaFolder;
use App\Models\MediaTag;
use App\Services\Media\MediaStorageService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MediaManager extends Component
{
    use WithFileUploads, WithPagination;

    // Filters & Navigation
    public ?int $currentFolderId = null;
    public string $search = '';
    public string $typeFilter = 'all';

    // File Upload State
    public $uploads = [];

    // Modals
    public bool $showFolderModal = false;
    public string $newFolderName = '';

    public bool $showDetailModal = false;
    public ?int $selectedAssetId = null;
    public ?MediaAsset $selectedAsset = null;
    public string $newTagName = '';

    protected function rules(): array
    {
        return [
            'uploads.*' => ['file', 'max:102400'], // 100MB per file
        ];
    }

    public function selectFolder(?int $folderId): void
    {
        $this->currentFolderId = $folderId;
        $this->resetPage();
    }

    public function createFolder(MediaStorageService $storageService): void
    {
        $this->validate([
            'newFolderName' => ['required', 'string', 'max:100'],
        ]);

        $workspace = app('current_workspace');
        $storageService->createFolder($workspace, $this->newFolderName, $this->currentFolderId);

        session()->flash('success', "Folder '{$this->newFolderName}' created.");
        $this->newFolderName = '';
        $this->showFolderModal = false;
    }

    public function updatedUploads(MediaStorageService $storageService): void
    {
        $this->validate([
            'uploads.*' => ['required', 'file', 'max:102400'],
        ]);

        $workspace = app('current_workspace');
        $uploader = auth()->user();

        $count = 0;
        foreach ($this->uploads as $file) {
            $storageService->storeUploadedFile($file, $workspace, $uploader, $this->currentFolderId);
            $count++;
        }

        $this->uploads = [];
        session()->flash('success', "Uploaded {$count} media file(s) successfully!");
    }

    public function openDetailModal(int $assetId): void
    {
        $this->selectedAssetId = $assetId;
        $this->selectedAsset = MediaAsset::with('tags')->findOrFail($assetId);
        $this->showDetailModal = true;
    }

    public function addTagToAsset(): void
    {
        if (empty(trim($this->newTagName)) || !$this->selectedAsset) {
            return;
        }

        $workspace = app('current_workspace');
        $tag = MediaTag::firstOrCreate([
            'workspace_id' => $workspace->id,
            'name' => strtolower(trim($this->newTagName)),
        ]);

        $this->selectedAsset->tags()->syncWithoutDetaching([$tag->id]);
        $this->selectedAsset = $this->selectedAsset->fresh(['tags']);
        $this->newTagName = '';
    }

    public function removeTagFromAsset(int $tagId): void
    {
        if ($this->selectedAsset) {
            $this->selectedAsset->tags()->detach($tagId);
            $this->selectedAsset = $this->selectedAsset->fresh(['tags']);
        }
    }

    public function deleteAsset(int $assetId, MediaStorageService $storageService): void
    {
        $asset = MediaAsset::findOrFail($assetId);
        $name = $asset->file_name;
        $storageService->deleteMediaAsset($asset);

        if ($this->selectedAssetId === $assetId) {
            $this->showDetailModal = false;
        }

        session()->flash('success', "Media file '{$name}' was deleted.");
    }

    public function render()
    {
        $workspace = app('current_workspace');

        // Query Folders
        $folders = MediaFolder::where('workspace_id', $workspace->id)
            ->where('parent_id', $this->currentFolderId)
            ->withCount('assets')
            ->get();

        $currentFolder = $this->currentFolderId ? MediaFolder::find($this->currentFolderId) : null;

        // Query Assets
        $assetQuery = MediaAsset::where('workspace_id', $workspace->id)
            ->where('folder_id', $this->currentFolderId);

        if (!empty($this->search)) {
            $assetQuery->where('file_name', 'like', '%' . $this->search . '%');
        }

        if ($this->typeFilter !== 'all') {
            $assetQuery->where('media_type', $this->typeFilter);
        }

        $assets = $assetQuery->latest()->paginate(16);

        return view('livewire.media-library.media-manager', [
            'folders' => $folders,
            'currentFolder' => $currentFolder,
            'assets' => $assets,
        ]);
    }
}

