<?php

namespace App\Livewire\Composer;

use App\Enums\PostStatus;
use App\Enums\SocialPlatform;
use App\Models\Client;
use App\Models\MediaAsset;
use App\Models\SocialProfile;
use App\Services\Publishing\PostCreationService;
use App\Services\Social\Registry\PlatformCapabilityRegistry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class PostComposer extends Component
{
    use WithFileUploads;

    // Left Panel Form State
    public ?int $selectedClientId = null;
    public array $selectedProfileIds = [];
    public string $baseCaption = '';
    public string $destinationLink = '';
    public array $selectedMediaAssetIds = [];

    // Scheduling State
    public string $publishMode = 'now'; // 'now', 'schedule', 'draft'
    public string $scheduledDate = '';
    public string $scheduledTime = '12:00';

    // Center Panel Variant Tabs & Overrides
    public string $activeTab = 'base'; // 'base' or numeric profileId
    public array $customCaptions = []; // [profileId => 'custom text']
    public array $customizedFlags = []; // [profileId => bool]
    public array $platformSettings = []; // [profileId => ['setting_key' => 'val']]
    public bool $showValidationWarnings = false;

    // Direct File Upload for Post Media & Custom Thumbnail
    public $directMediaUpload = null;
    public $directThumbnailUpload = null;

    // Media Library Selector Modal
    public bool $showMediaModal = false;
    public string $mediaSearch = '';

    // Custom Thumbnail Modal (YouTube & Video Posts)
    public bool $showThumbnailModal = false;
    public ?int $selectingThumbnailForProfileId = null;

    // AI Content Assistant State
    public bool $showAiModal = false;
    public string $aiAction = 'caption'; // 'caption', 'rewrite', 'hashtags'
    public string $aiPromptTopic = '';
    public string $aiTone = 'engaging'; // 'engaging', 'professional', 'humorous', 'urgent', 'educational'
    public string $aiGeneratedResult = '';
    public string $aiTargetMode = 'base'; // 'base' or numeric profileId

    public function mount()
    {
        $this->scheduledDate = now()->addDay()->format('Y-m-d');
        $this->scheduledTime = '10:00';

        // Pre-select first client if available
        $firstClient = Client::first();
        if ($firstClient) {
            $this->selectedClientId = $firstClient->id;
        }

        // Pre-select first 2 profiles if available
        $profiles = SocialProfile::where('publishing_enabled', true)->take(2)->get();
        foreach ($profiles as $p) {
            $this->selectedProfileIds[] = $p->id;
        }
    }

    public function toggleProfile(int $profileId): void
    {
        if (in_array($profileId, $this->selectedProfileIds, true)) {
            $this->selectedProfileIds = array_values(array_diff($this->selectedProfileIds, [$profileId]));
            if ($this->activeTab == (string)$profileId) {
                $this->activeTab = 'base';
            }
        } else {
            $this->selectedProfileIds[] = $profileId;
        }
    }

    public function selectAllProfiles(): void
    {
        $profiles = $this->getAvailableProfilesProperty();
        $this->selectedProfileIds = $profiles->pluck('id')->toArray();
    }

    public function deselectAllProfiles(): void
    {
        $this->selectedProfileIds = [];
        $this->activeTab = 'base';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        if ($tab !== 'base' && is_numeric($tab)) {
            $profileId = (int)$tab;
            $this->ensurePlatformSettingsInitialized($profileId);
            $this->customizedFlags[$profileId] = true;
            if (!isset($this->customCaptions[$profileId]) || empty($this->customCaptions[$profileId])) {
                $this->customCaptions[$profileId] = $this->baseCaption;
            }
        }
    }

    public function updatedBaseCaption($value): void
    {
        $firstLine = trim(strtok($value, "\r\n") ?: '');
        if (!empty($firstLine)) {
            foreach ($this->selectedProfileIds as $pid) {
                if (isset($this->platformSettings[$pid])) {
                    $currTitle = trim($this->platformSettings[$pid]['title'] ?? '');
                    if (empty($currTitle) || $currTitle === 'New Video' || $currTitle === 'New YouTube Video') {
                        $this->platformSettings[$pid]['title'] = mb_substr($firstLine, 0, 100);
                    }
                }
            }
        }
    }

    public function toggleCustomizeVariant(int $profileId): void
    {
        $isCustom = !empty($this->customizedFlags[$profileId]);
        if ($isCustom) {
            $this->customizedFlags[$profileId] = false;
            unset($this->customCaptions[$profileId]);
        } else {
            $this->customizedFlags[$profileId] = true;
            $this->customCaptions[$profileId] = $this->baseCaption;

            $profile = SocialProfile::find($profileId);
            if ($profile && $profile->platform === 'youtube' && !isset($this->platformSettings[$profileId])) {
                $firstLine = strtok($this->baseCaption, "\r\n");
                $this->platformSettings[$profileId] = [
                    'title' => mb_substr($firstLine ?: '', 0, 100),
                    'privacy_status' => 'public',
                    'category_id' => '22',
                    'tags' => '',
                    'made_for_kids' => false,
                    'thumbnail_url' => '',
                ];
            }
        }
    }

    public function openThumbnailModal(int $profileId): void
    {
        $this->selectingThumbnailForProfileId = $profileId;
        $this->showThumbnailModal = true;
    }

    public function updatedDirectThumbnailUpload(): void
    {
        $this->validate([
            'directThumbnailUpload' => 'image|max:5120',
        ]);

        if ($this->directThumbnailUpload && $this->selectingThumbnailForProfileId) {
            $path = $this->directThumbnailUpload->store('thumbnails', 'public');
            $url = Storage::disk('public')->url($path);
            $this->selectThumbnailAsset($this->selectingThumbnailForProfileId, $url);
            $this->directThumbnailUpload = null;
        }
    }

    public function updatedDirectMediaUpload(): void
    {
        $this->validate([
            'directMediaUpload' => 'file|max:102400', // max 100MB
        ]);

        if ($this->directMediaUpload) {
            $workspace = app('current_workspace');
            $file = $this->directMediaUpload;
            $mime = $file->getMimeType() ?: 'application/octet-stream';
            $mediaType = str_starts_with($mime, 'video/') ? 'video' : 'image';
            $path = $file->store('media/' . $workspace->id, 'public');

            $asset = MediaAsset::create([
                'workspace_id' => $workspace->id,
                'client_id' => $this->selectedClientId,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'media_type' => $mediaType,
                'mime_type' => $mime,
                'file_size_bytes' => $file->getSize(),
            ]);

            $this->selectedMediaAssetIds[] = $asset->id;
            $this->directMediaUpload = null;
        }
    }

    public function ensurePlatformSettingsInitialized(int $profileId): void
    {
        if (isset($this->platformSettings[$profileId]) && !empty($this->platformSettings[$profileId]['post_type'])) {
            return;
        }

        $profile = SocialProfile::find($profileId);
        if (!$profile) return;

        $firstLine = strtok($this->baseCaption, "\r\n");
        $plat = $profile->platform instanceof SocialPlatform ? $profile->platform->value : $profile->platform;

        if ($plat === 'youtube') {
            $this->platformSettings[$profileId] = array_merge([
                'post_type' => 'video',
                'title' => mb_substr($firstLine ?: 'New YouTube Video', 0, 100),
                'privacy_status' => 'public',
                'category_id' => '22',
                'tags' => '',
                'made_for_kids' => false,
                'thumbnail_url' => '',
            ], $this->platformSettings[$profileId] ?? []);
        } elseif ($plat === 'instagram') {
            $this->platformSettings[$profileId] = array_merge([
                'post_type' => 'feed',
                'share_to_feed' => true,
                'cover_image_url' => '',
            ], $this->platformSettings[$profileId] ?? []);
        } elseif ($plat === 'facebook') {
            $this->platformSettings[$profileId] = array_merge([
                'post_type' => 'feed',
            ], $this->platformSettings[$profileId] ?? []);
        } elseif ($plat === 'linkedin') {
            $this->platformSettings[$profileId] = array_merge([
                'post_type' => 'post',
            ], $this->platformSettings[$profileId] ?? []);
        }
    }

    public function selectThumbnailAsset(int $profileId, string $publicUrl): void
    {
        $this->ensurePlatformSettingsInitialized($profileId);
        $this->platformSettings[$profileId]['thumbnail_url'] = $publicUrl;
        $this->showThumbnailModal = false;
        $this->selectingThumbnailForProfileId = null;
    }

    public function removeThumbnail(int $profileId): void
    {
        if (isset($this->platformSettings[$profileId])) {
            $this->platformSettings[$profileId]['thumbnail_url'] = '';
        }
    }

    public function openMediaModal(): void
    {
        $this->showMediaModal = true;
    }

    public function toggleMediaAsset(int $assetId): void
    {
        if (in_array($assetId, $this->selectedMediaAssetIds, true)) {
            $this->selectedMediaAssetIds = array_values(array_diff($this->selectedMediaAssetIds, [$assetId]));
        } else {
            $this->selectedMediaAssetIds[] = $assetId;
        }
    }

    public function removeMediaAsset(int $assetId): void
    {
        $this->selectedMediaAssetIds = array_values(array_diff($this->selectedMediaAssetIds, [$assetId]));
    }

    public function getEffectiveCaption(SocialProfile $profile): string
    {
        if (!empty($this->customizedFlags[$profile->id]) && isset($this->customCaptions[$profile->id])) {
            return $this->customCaptions[$profile->id];
        }
        return $this->baseCaption;
    }

    public function getCharacterLimit(SocialProfile $profile): int
    {
        $caps = PlatformCapabilityRegistry::getCapabilities($profile->platform);
        return $caps['text_limit'] ?? 5000;
    }

    public function getValidationErrorsProperty(): array
    {
        $errors = [];

        if (empty($this->selectedProfileIds)) {
            $errors[] = 'Select at least one social profile to target.';
        }

        $profiles = SocialProfile::whereIn('id', $this->selectedProfileIds)->get();

        foreach ($profiles as $profile) {
            $plat = $profile->platform instanceof SocialPlatform ? $profile->platform->value : (string)$profile->platform;
            $caption = $this->getEffectiveCaption($profile);
            $limit = $this->getCharacterLimit($profile);

            if (mb_strlen($caption) > $limit) {
                $errors[] = "Caption for {$profile->name} exceeds {$plat} limit of {$limit} characters (current: " . mb_strlen($caption) . ").";
            }

            if ($plat === 'instagram' && empty($this->selectedMediaAssetIds)) {
                $errors[] = "Instagram profile '{$profile->name}' requires at least one image or video.";
            }

            if ($plat === 'youtube') {
                if (empty($this->selectedMediaAssetIds)) {
                    $errors[] = "YouTube profile '{$profile->name}' requires an uploaded video (MP4/MOV).";
                }
                $ytSettings = $this->platformSettings[$profile->id] ?? [];
                $ytTitle = trim($ytSettings['title'] ?? '');
                $captionTitle = trim(strtok($this->baseCaption, "\r\n") ?: '');
                if (empty($ytTitle) && empty($captionTitle)) {
                    $errors[] = "YouTube profile '{$profile->name}' requires a Video Title.";
                }
            }
        }

        return $errors;
    }

    public function savePost(string $action, PostCreationService $postCreationService)
    {
        $this->showValidationWarnings = true;

        // Auto-fill YouTube title from caption if still empty
        foreach ($this->selectedProfileIds as $pid) {
            $p = SocialProfile::find($pid);
            $plat = $p?->platform instanceof SocialPlatform ? $p->platform->value : (string)$p?->platform;
            if ($plat === 'youtube') {
                $currTitle = trim($this->platformSettings[$pid]['title'] ?? '');
                if (empty($currTitle)) {
                    $firstLine = trim(strtok($this->baseCaption, "\r\n") ?: '');
                    $this->platformSettings[$pid]['title'] = !empty($firstLine) ? mb_substr($firstLine, 0, 100) : 'Social Video';
                }
            }
        }

        $validationErrors = $this->getValidationErrorsProperty();
        if (!empty($validationErrors) && $action !== 'draft') {
            session()->flash('error', $validationErrors[0]);
            return;
        }

        if (empty($this->selectedProfileIds)) {
            session()->flash('error', 'Please select at least one social profile.');
            return;
        }

        $workspace = app('current_workspace');
        $tz = $workspace->timezone ?? config('app.timezone', 'Asia/Kolkata');
        $author = auth()->user();

        $status = match ($action) {
            'draft' => PostStatus::DRAFT,
            'schedule' => PostStatus::SCHEDULED,
            'now' => PostStatus::QUEUED,
        };

        $scheduledAt = null;
        if ($action === 'schedule') {
            $scheduledAt = Carbon::parse("{$this->scheduledDate} {$this->scheduledTime}", $tz);
        } elseif ($action === 'now') {
            $scheduledAt = now();
        }

        $post = $postCreationService->createPost($workspace, $author, [
            'client_id' => $this->selectedClientId,
            'base_caption' => $this->baseCaption,
            'destination_link' => $this->destinationLink,
            'profile_ids' => $this->selectedProfileIds,
            'variant_captions' => $this->customCaptions,
            'variant_settings' => $this->platformSettings,
            'media_asset_ids' => $this->selectedMediaAssetIds,
            'status' => $status,
            'scheduled_at' => $scheduledAt,
            'timezone' => $tz,
        ]);

        if ($action === 'now') {
            foreach ($post->variants as $variant) {
                if (app()->environment('testing')) {
                    \App\Jobs\Publishing\PublishPostVariantJob::dispatch($variant->id, (string) \Illuminate\Support\Str::uuid());
                } else {
                    try {
                        \App\Jobs\Publishing\PublishPostVariantJob::dispatchSync($variant->id, (string) \Illuminate\Support\Str::uuid());
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Immediate publish error: " . $e->getMessage());
                        $variant->update([
                            'status' => \App\Enums\PostStatus::FAILED,
                            'error_message' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        $message = match ($action) {
            'draft' => "Post draft saved successfully!",
            'schedule' => "Post scheduled for " . $scheduledAt->format('M d, Y h:i A') . "!",
            'now' => "Post published immediately!",
        };

        session()->flash('success', $message);

        return $this->redirect(route('posts.index'), navigate: true);
    }

    public function getAvailableProfilesProperty()
    {
        $query = SocialProfile::where('publishing_enabled', true);
        if ($this->selectedClientId) {
            $query->where(function ($q) {
                $q->where('client_id', $this->selectedClientId)
                  ->orWhereNull('client_id');
            });
        }
        return $query->with('client')->get();
    }

    public function openAiModal(string $target = 'base'): void
    {
        $this->aiTargetMode = $target;
        $this->aiGeneratedResult = '';
        if ($target === 'base' && !empty($this->baseCaption)) {
            $this->aiPromptTopic = $this->baseCaption;
        } elseif ($target !== 'base' && !empty($this->customCaptions[(int)$target])) {
            $this->aiPromptTopic = $this->customCaptions[(int)$target];
        }
        $this->showAiModal = true;
    }

    public function closeAiModal(): void
    {
        $this->showAiModal = false;
    }

    public function generateAiContent(\App\Services\AI\AIAssistantService $aiService): void
    {
        $workspace = auth()->user()->activeWorkspace() ?? app('current_workspace');

        if ($this->aiAction === 'caption') {
            $this->aiGeneratedResult = $aiService->generateCaption(
                $workspace,
                $this->aiPromptTopic ?: 'Exciting business news and special announcement',
                $this->aiTone
            );
        } elseif ($this->aiAction === 'rewrite') {
            $this->aiGeneratedResult = $aiService->rewriteContent(
                $workspace,
                $this->aiPromptTopic ?: $this->baseCaption,
                $this->aiTone === 'urgent' ? 'punchier' : 'shorter'
            );
        } elseif ($this->aiAction === 'hashtags') {
            $tags = $aiService->generateHashtags(
                $workspace,
                $this->aiPromptTopic ?: $this->baseCaption
            );
            $this->aiGeneratedResult = implode(' ', $tags);
        }
    }

    public function applyAiResult(): void
    {
        if (empty($this->aiGeneratedResult)) {
            return;
        }

        if ($this->aiTargetMode === 'base') {
            $this->baseCaption = $this->aiGeneratedResult;
        } else {
            $profileId = (int) $this->aiTargetMode;
            $this->customCaptions[$profileId] = $this->aiGeneratedResult;
            $this->customizedFlags[$profileId] = true;
        }

        $this->closeAiModal();
    }

    public function render()
    {
        $clients = Client::all();
        $availableProfiles = $this->getAvailableProfilesProperty();
        $selectedMediaAssets = MediaAsset::whereIn('id', $this->selectedMediaAssetIds)->get();

        // Query media modal assets
        $workspace = app('current_workspace');
        $modalMediaAssets = MediaAsset::where('workspace_id', $workspace->id)
            ->when(!empty($this->mediaSearch), fn ($q) => $q->where('file_name', 'like', "%{$this->mediaSearch}%"))
            ->latest()
            ->take(24)
            ->get();

        // Selected preview profile
        $previewProfile = null;
        if ($this->activeTab !== 'base') {
            $previewProfile = SocialProfile::find((int)$this->activeTab);
        } elseif (!empty($this->selectedProfileIds)) {
            $previewProfile = SocialProfile::find($this->selectedProfileIds[0]);
        }

        return view('livewire.composer.post-composer', [
            'clients' => $clients,
            'availableProfiles' => $availableProfiles,
            'selectedMediaAssets' => $selectedMediaAssets,
            'modalMediaAssets' => $modalMediaAssets,
            'previewProfile' => $previewProfile,
        ]);
    }
}

