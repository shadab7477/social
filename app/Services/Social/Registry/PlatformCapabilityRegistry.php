<?php

namespace App\Services\Social\Registry;

use App\Enums\SocialPlatform;

class PlatformCapabilityRegistry
{
    /**
     * Get the standardized capability definition for any platform.
     */
    public static function getCapabilities(SocialPlatform|string $platform): array
    {
        $platformEnum = is_string($platform) ? SocialPlatform::tryFrom($platform) : $platform;

        if (!$platformEnum) {
            return [];
        }

        return match ($platformEnum) {
            SocialPlatform::FACEBOOK => [
                'text_limit' => 63206,
                'supports_text' => true,
                'supports_images' => true,
                'max_images' => 10,
                'supports_video' => true,
                'supports_reels' => true,
                'supports_stories' => true,
                'supports_links' => true,
                'supports_scheduling' => true,
                'requires_approval_review' => true,
            ],
            SocialPlatform::INSTAGRAM => [
                'text_limit' => 2200,
                'supports_text' => false, // Instagram requires media
                'supports_images' => true,
                'max_images' => 10,
                'supports_carousel' => true,
                'supports_video' => true,
                'supports_reels' => true,
                'supports_stories' => false,
                'supports_links' => false,
                'supports_scheduling' => true,
                'requires_business_account' => true,
            ],
            SocialPlatform::LINKEDIN => [
                'text_limit' => 3000,
                'supports_text' => true,
                'supports_images' => true,
                'max_images' => 9,
                'supports_video' => true,
                'supports_documents' => true,
                'supports_links' => true,
                'supports_scheduling' => true,
            ],
            SocialPlatform::YOUTUBE => [
                'text_limit' => 5000,
                'supports_text' => false,
                'supports_images' => false,
                'supports_video' => true,
                'supports_shorts' => true,
                'supports_resumable_upload' => true,
                'daily_quota_tracked' => true,
            ],
            SocialPlatform::TIKTOK => [
                'text_limit' => 2200,
                'supports_text' => false,
                'supports_images' => true,
                'supports_video' => true,
                'supports_direct_post' => true,
                'requires_commercial_audit' => true,
            ],
            SocialPlatform::PINTEREST => [
                'text_limit' => 500,
                'title_limit' => 100,
                'supports_text' => false,
                'supports_images' => true,
                'supports_video' => true,
                'requires_board' => true,
                'requires_destination_link' => true,
            ],
            SocialPlatform::X => [
                'text_limit' => 280,
                'supports_text' => true,
                'supports_images' => true,
                'max_images' => 4,
                'supports_video' => true,
                'rate_limits_enforced' => true,
            ],
            SocialPlatform::THREADS => [
                'text_limit' => 500,
                'supports_text' => true,
                'supports_images' => true,
                'max_images' => 10,
                'supports_video' => true,
                'container_based' => true,
            ],
            SocialPlatform::GOOGLE_BUSINESS => [
                'text_limit' => 1500,
                'supports_text' => true,
                'supports_images' => true,
                'supports_call_to_action' => true,
                'supports_locations' => true,
            ],
            SocialPlatform::BLUESKY => [
                'text_limit' => 300,
                'supports_text' => true,
                'supports_images' => true,
                'max_images' => 4,
                'uses_at_protocol' => true,
            ],
        };
    }
}

