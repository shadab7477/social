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
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PostComposer extends Component
{
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

    // Media Library Selector Modal
    public bool $showMediaModal = false;
    public string $mediaSearch = '';

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

    public function toggleCustomizeVariant(int $profileId): void
    {
        $isCustom = !empty($this->customizedFlags[$profileId]);
        if ($isCustom) {
            $this->customizedFlags[$profileId] = false;
            unset($this->customCaptions[$profileId]);
        } else {
            $this->customizedFlags[$profileId] = true;
            $this->customCaptions[$profileId] = $this->baseCaption;
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
            $caption = $this->getEffectiveCaption($profile);
            $limit = $this->getCharacterLimit($profile);

            if (mb_strlen($caption) > $limit) {
                $errors[] = "Caption for {$profile->name} exceeds {$profile->platform} limit of {$limit} characters (current: " . mb_strlen($caption) . ").";
            }

            if ($profile->platform === 'instagram' && empty($this->selectedMediaAssetIds)) {
                $errors[] = "Instagram profile '{$profile->name}' requires at least one image or video.";
            }

            if ($profile->platform === 'youtube' && empty($this->selectedMediaAssetIds)) {
                $errors[] = "YouTube profile '{$profile->name}' requires an uploaded video.";
            }
        }

        return $errors;
    }

    public function savePost(string $action, PostCreationService $postCreationService)
    {
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
        $author = auth()->user();

        $status = match ($action) {
            'draft' => PostStatus::DRAFT,
            'schedule' => PostStatus::SCHEDULED,
            'now' => PostStatus::QUEUED,
        };

        $scheduledAt = null;
        if ($action === 'schedule') {
            $scheduledAt = Carbon::parse("{$this->scheduledDate} {$this->scheduledTime}");
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
        ]);

        $message = match ($action) {
            'draft' => "Post draft saved successfully!",
            'schedule' => "Post scheduled for " . $scheduledAt->format('M d, Y h:i A') . "!",
            'now' => "Post queued for immediate publishing!",
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

