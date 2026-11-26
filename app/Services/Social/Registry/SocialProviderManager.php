<?php

namespace App\Services\Social\Registry;

use App\Enums\SocialPlatform;
use App\Services\Social\Contracts\OAuthProviderInterface;
use App\Services\Social\Contracts\ProfileDiscoveryInterface;
use App\Services\Social\Contracts\PublishingProviderInterface;
use App\Services\Social\Contracts\SocialProviderInterface;
use App\Services\Social\Facebook\FacebookOAuthService;
use App\Services\Social\Facebook\FacebookPublishingService;
use App\Services\Social\GoogleBusiness\GoogleBusinessOAuthService;
use App\Services\Social\GoogleBusiness\GoogleBusinessPublishingService;
use App\Services\Social\Instagram\InstagramPublishingService;
use App\Services\Social\LinkedIn\LinkedInOAuthService;
use App\Services\Social\LinkedIn\LinkedInPublishingService;
use App\Services\Social\Pinterest\PinterestOAuthService;
use App\Services\Social\Pinterest\PinterestPublishingService;
use App\Services\Social\YouTube\YouTubeOAuthService;
use App\Services\Social\YouTube\YouTubePublishingService;
use InvalidArgumentException;

class SocialProviderManager
{
    /**
     * Resolves the OAuth provider adapter for the given platform.
     */
    public function getOAuthProvider(SocialPlatform|string $platform): OAuthProviderInterface&ProfileDiscoveryInterface
    {
        $platformEnum = is_string($platform) ? SocialPlatform::from($platform) : $platform;

        return match ($platformEnum) {
            SocialPlatform::FACEBOOK,
            SocialPlatform::INSTAGRAM => app(FacebookOAuthService::class),
            SocialPlatform::LINKEDIN => app(LinkedInOAuthService::class),
            SocialPlatform::YOUTUBE => app(YouTubeOAuthService::class),
            SocialPlatform::GOOGLE_BUSINESS => app(GoogleBusinessOAuthService::class),
            SocialPlatform::PINTEREST => app(PinterestOAuthService::class),
            default => throw new InvalidArgumentException("OAuth integration for platform '{$platformEnum->value}' will be registered in subsequent phases."),
        };
    }

    /**
     * Resolves the publishing adapter for the given platform.
     */
    public function getPublishingProvider(SocialPlatform|string $platform): PublishingProviderInterface
    {
        $platformEnum = is_string($platform) ? SocialPlatform::from($platform) : $platform;

        return match ($platformEnum) {
            SocialPlatform::FACEBOOK => app(FacebookPublishingService::class),
            SocialPlatform::INSTAGRAM => app(InstagramPublishingService::class),
            SocialPlatform::LINKEDIN => app(LinkedInPublishingService::class),
            SocialPlatform::YOUTUBE => app(YouTubePublishingService::class),
            SocialPlatform::GOOGLE_BUSINESS => app(GoogleBusinessPublishingService::class),
            SocialPlatform::PINTEREST => app(PinterestPublishingService::class),
            default => throw new InvalidArgumentException("Publishing adapter for '{$platformEnum->value}' not registered."),
        };
    }

    /**
     * Resolves the analytics provider adapter for the given platform.
     */
    public function getAnalyticsProvider(SocialPlatform|string $platform): \App\Services\Social\Contracts\AnalyticsProviderInterface
    {
        $platformEnum = is_string($platform) ? SocialPlatform::from($platform) : $platform;

        return match ($platformEnum) {
            SocialPlatform::FACEBOOK => app(\App\Services\Social\Facebook\FacebookAnalyticsService::class),
            SocialPlatform::INSTAGRAM => app(\App\Services\Social\Instagram\InstagramAnalyticsService::class),
            SocialPlatform::YOUTUBE => app(\App\Services\Social\YouTube\YouTubeAnalyticsService::class),
            default => app(\App\Services\Social\Facebook\FacebookAnalyticsService::class),
        };
    }

    /**
     * Resolves the general provider service.
     */
    public function getProvider(SocialPlatform|string $platform): SocialProviderInterface
    {
        $platformEnum = is_string($platform) ? SocialPlatform::from($platform) : $platform;

        return match ($platformEnum) {
            SocialPlatform::FACEBOOK => app(FacebookPublishingService::class),
            SocialPlatform::INSTAGRAM => app(InstagramPublishingService::class),
            SocialPlatform::LINKEDIN => app(LinkedInPublishingService::class),
            SocialPlatform::YOUTUBE => app(YouTubePublishingService::class),
            SocialPlatform::GOOGLE_BUSINESS => app(GoogleBusinessPublishingService::class),
            SocialPlatform::PINTEREST => app(PinterestPublishingService::class),
            default => throw new InvalidArgumentException("Provider service for '{$platformEnum->value}' not registered."),
        };
    }
}

