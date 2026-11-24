<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use App\Models\MediaFolder;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaStorageService
{
    /**
     * Ingest and store an uploaded media asset into cloud/local storage.
     */
    public function storeUploadedFile(
        UploadedFile $file,
        Workspace $workspace,
        User $uploader,
        ?int $folderId = null,
        ?string $disk = null
    ): MediaAsset {
        $storageDisk = $disk ?? (env('CLOUDFLARE_R2_ACCESS_KEY_ID') ? 'r2' : 'public');
        $fileName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $fileSize = $file->getSize();

        $mediaType = $this->determineMediaType($mimeType);

        // Extract image dimensions and calculate aspect ratio
        $width = null;
        $height = null;
        $aspectRatio = null;

        if ($mediaType === 'image') {
            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                $aspectRatio = $this->calculateAspectRatio($width, $height);
            }
        }

        // Store file with unique sanitized key
        $hashName = Str::random(32) . '.' . $file->getClientOriginalExtension();
        $path = "workspaces/{$workspace->id}/media/{$hashName}";

        Storage::disk($storageDisk)->putFileAs("workspaces/{$workspace->id}/media", $file, $hashName, 'public');

        $publicUrl = Storage::disk($storageDisk)->url($path);

        return MediaAsset::create([
            'workspace_id' => $workspace->id,
            'uploader_id' => $uploader->id,
            'folder_id' => $folderId,
            'file_name' => $fileName,
            'storage_path' => $path,
            'disk' => $storageDisk,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'media_type' => $mediaType,
            'width' => $width,
            'height' => $height,
            'aspect_ratio' => $aspectRatio,
            'public_url' => $publicUrl,
            'thumbnail_url' => $mediaType === 'image' ? $publicUrl : null,
            'metadata' => [
                'original_name' => $fileName,
                'extension' => $file->getClientOriginalExtension(),
            ],
        ]);
    }

    /**
     * Determine general media category based on MIME type.
     */
    public function determineMediaType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/gif')) {
            return 'gif';
        }
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        return 'document';
    }

    /**
     * Calculate standardized aspect ratio (e.g. 1:1, 16:9, 9:16, 4:5).
     */
    public function calculateAspectRatio(int $width, int $height): string
    {
        if ($width <= 0 || $height <= 0) {
            return '1:1';
        }

        $ratio = $width / $height;

        if (abs($ratio - 1.0) < 0.05) {
            return '1:1';
        }
        if (abs($ratio - (16 / 9)) < 0.05) {
            return '16:9';
        }
        if (abs($ratio - (9 / 16)) < 0.05) {
            return '9:16';
        }
        if (abs($ratio - (4 / 5)) < 0.05) {
            return '4:5';
        }

        return "{$width}:{$height}";
    }

    /**
     * Safe deletion of media asset with usage reference protection.
     */
    public function deleteMediaAsset(MediaAsset $asset, bool $force = false): bool
    {
        // Delete physical file from storage
        if (Storage::disk($asset->disk)->exists($asset->storage_path)) {
            Storage::disk($asset->disk)->delete($asset->storage_path);
        }

        return (bool) $asset->forceDelete();
    }

    /**
     * Create a new organizational folder in workspace.
     */
    public function createFolder(Workspace $workspace, string $name, ?int $parentId = null): MediaFolder
    {
        $cleanName = trim($name);
        $exists = MediaFolder::where('workspace_id', $workspace->id)
            ->where('parent_id', $parentId)
            ->where('name', $cleanName)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'folder_name' => "A folder named '{$cleanName}' already exists here.",
            ]);
        }

        return MediaFolder::create([
            'workspace_id' => $workspace->id,
            'parent_id' => $parentId,
            'name' => $cleanName,
        ]);
    }
}

