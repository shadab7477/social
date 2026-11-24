<?php

namespace Tests\Feature;

use App\Enums\SocialPlatform;
use App\Livewire\MediaLibrary\MediaManager;
use App\Models\MediaAsset;
use App\Models\MediaFolder;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Media\MediaStorageService;
use App\Services\Media\MediaValidationService;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('r2');

        $this->user = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $this->workspace = $workspaceManager->createWorkspace($this->user, 'Visual Studio Agency');

        $this->actingAs($this->user);
        session(['active_workspace_id' => $this->workspace->id]);
        app()->instance('current_workspace', $this->workspace);
    }

    /**
     * Test media folder creation.
     */
    public function test_can_create_media_folder(): void
    {
        Livewire::test(MediaManager::class)
            ->set('newFolderName', 'Campaign 2026')
            ->call('createFolder')
            ->assertHasNoErrors();

        $folder = MediaFolder::where('name', 'Campaign 2026')->first();
        $this->assertNotNull($folder);
        $this->assertEquals($this->workspace->id, $folder->workspace_id);
    }

    /**
     * Test uploading image file extracts dimensions and aspect ratio.
     */
    public function test_uploading_image_extracts_dimensions_and_stores_file(): void
    {
        $file = UploadedFile::fake()->image('banner_hero.jpg', 1200, 675); // 16:9 aspect ratio

        $storageService = app(MediaStorageService::class);
        $asset = $storageService->storeUploadedFile($file, $this->workspace, $this->user, null, 'public');

        $this->assertNotNull($asset);
        $this->assertEquals('banner_hero.jpg', $asset->file_name);
        $this->assertEquals('image', $asset->media_type);
        $this->assertEquals(1200, $asset->width);
        $this->assertEquals(675, $asset->height);
        $this->assertEquals('16:9', $asset->aspect_ratio);
        $this->assertNotEmpty($asset->public_url);

        Storage::disk('public')->assertExists($asset->storage_path);
    }

    /**
     * Test aspect ratio calculation for Instagram square (1:1) and portrait (4:5).
     */
    public function test_aspect_ratio_calculation(): void
    {
        $service = app(MediaStorageService::class);

        $this->assertEquals('1:1', $service->calculateAspectRatio(1080, 1080));
        $this->assertEquals('16:9', $service->calculateAspectRatio(1920, 1080));
        $this->assertEquals('9:16', $service->calculateAspectRatio(1080, 1920));
        $this->assertEquals('4:5', $service->calculateAspectRatio(1080, 1350));
    }

    /**
     * Test tagging media assets.
     */
    public function test_can_add_tags_to_media_asset(): void
    {
        $file = UploadedFile::fake()->image('dish.png', 800, 800);
        $storageService = app(MediaStorageService::class);
        $asset = $storageService->storeUploadedFile($file, $this->workspace, $this->user, null, 'public');

        Livewire::test(MediaManager::class)
            ->call('openDetailModal', $asset->id)
            ->set('newTagName', 'culinary')
            ->call('addTagToAsset');

        $asset = $asset->fresh(['tags']);
        $this->assertCount(1, $asset->tags);
        $this->assertEquals('culinary', $asset->tags->first()->name);
    }

    /**
     * Test platform validation rules for Instagram and X.
     */
    public function test_platform_media_validation(): void
    {
        $validator = app(MediaValidationService::class);

        // Document file (PDF)
        $pdfFile = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');
        $storageService = app(MediaStorageService::class);
        $pdfAsset = $storageService->storeUploadedFile($pdfFile, $this->workspace, $this->user, null, 'public');

        // Instagram should reject PDF
        $igValidation = $validator->validateForPlatform($pdfAsset, SocialPlatform::INSTAGRAM);
        $this->assertFalse($igValidation['valid']);
        $this->assertStringContainsString('Instagram supports only image and video', $igValidation['errors'][0]);

        // LinkedIn should accept PDF
        $liValidation = $validator->validateForPlatform($pdfAsset, SocialPlatform::LINKEDIN);
        $this->assertTrue($liValidation['valid']);
    }

    /**
     * Test multi-tenant isolation on media assets.
     */
    public function test_media_assets_are_isolated_by_workspace(): void
    {
        $userB = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspaceB = $workspaceManager->createWorkspace($userB, 'Beta Design Lab');

        $storageService = app(MediaStorageService::class);
        $fileA = UploadedFile::fake()->image('alpha_design.jpg', 600, 600);
        $fileB = UploadedFile::fake()->image('beta_design.jpg', 600, 600);

        $assetA = $storageService->storeUploadedFile($fileA, $this->workspace, $this->user, null, 'public');
        $assetB = $storageService->storeUploadedFile($fileB, $workspaceB, $userB, null, 'public');

        // Workspace A context
        $this->actingAs($this->user);
        session(['active_workspace_id' => $this->workspace->id]);
        app()->instance('current_workspace', $this->workspace);

        $visibleAssetsA = MediaAsset::all();
        $this->assertTrue($visibleAssetsA->contains($assetA));
        $this->assertFalse($visibleAssetsA->contains($assetB));
    }
}

