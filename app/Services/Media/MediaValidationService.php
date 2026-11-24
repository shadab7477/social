<?php

namespace App\Services\Media;

use App\Enums\SocialPlatform;
use App\Models\MediaAsset;

class MediaValidationService
{
    /**
     * Check if a media asset satisfies platform-specific specifications.
     */
    public function validateForPlatform(MediaAsset $asset, SocialPlatform|string $platform): array
    {
        $platformEnum = is_string($platform) ? SocialPlatform::from($platform) : $platform;
        $errors = [];

        switch ($platformEnum) {
            case SocialPlatform::INSTAGRAM:
                if (!in_array($asset->media_type, ['image', 'video'], true)) {
                    $errors[] = 'Instagram supports only image and video formats.';
                }
                if ($asset->isImage() && $asset->file_size > 8 * 1024 * 1024) {
                    $errors[] = 'Instagram images must not exceed 8MB.';
                }
                break;

            case SocialPlatform::FACEBOOK:
                if (!in_array($asset->media_type, ['image', 'video', 'gif'], true)) {
                    $errors[] = 'Facebook supports images, videos, and GIFs.';
                }
                if ($asset->file_size > 500 * 1024 * 1024) {
                    $errors[] = 'Facebook media cannot exceed 500MB.';
                }
                break;

            case SocialPlatform::X:
                if ($asset->isImage() && $asset->file_size > 5 * 1024 * 1024) {
                    $errors[] = 'X (Twitter) image size limit is 5MB.';
                }
                if ($asset->isVideo() && $asset->file_size > 512 * 1024 * 1024) {
                    $errors[] = 'X (Twitter) video size limit is 512MB.';
                }
                break;

            case SocialPlatform::LINKEDIN:
                if (!in_array($asset->media_type, ['image', 'video', 'document', 'gif'], true)) {
                    $errors[] = 'LinkedIn supports images, videos, GIFs, and PDF documents.';
                }
                break;

            case SocialPlatform::YOUTUBE:
                if (!$asset->isVideo()) {
                    $errors[] = 'YouTube requires video files (MP4/MOV).';
                }
                break;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}

