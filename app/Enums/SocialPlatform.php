<?php

namespace App\Enums;

enum SocialPlatform: string
{
    case FACEBOOK = 'facebook';
    case INSTAGRAM = 'instagram';
    case LINKEDIN = 'linkedin';
    case YOUTUBE = 'youtube';
    case TIKTOK = 'tiktok';
    case PINTEREST = 'pinterest';
    case X = 'x';
    case THREADS = 'threads';
    case GOOGLE_BUSINESS = 'google_business';
    case BLUESKY = 'bluesky';

    public function label(): string
    {
        return match ($this) {
            self::FACEBOOK => 'Facebook',
            self::INSTAGRAM => 'Instagram',
            self::LINKEDIN => 'LinkedIn',
            self::YOUTUBE => 'YouTube',
            self::TIKTOK => 'TikTok',
            self::PINTEREST => 'Pinterest',
            self::X => 'X (Twitter)',
            self::THREADS => 'Threads',
            self::GOOGLE_BUSINESS => 'Google Business Profile',
            self::BLUESKY => 'Bluesky',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FACEBOOK => 'bi-facebook',
            self::INSTAGRAM => 'bi-instagram',
            self::LINKEDIN => 'bi-linkedin',
            self::YOUTUBE => 'bi-youtube',
            self::TIKTOK => 'bi-tiktok',
            self::PINTEREST => 'bi-pin-angle-fill',
            self::X => 'bi-twitter-x',
            self::THREADS => 'bi-at',
            self::GOOGLE_BUSINESS => 'bi-google',
            self::BLUESKY => 'bi-cloud-sun-fill',
        };
    }

    public function cssClass(): string
    {
        return match ($this) {
            self::FACEBOOK => 'bg-primary text-white',
            self::INSTAGRAM => 'bg-danger text-white',
            self::LINKEDIN => 'bg-info text-dark',
            self::YOUTUBE => 'bg-danger text-white',
            self::TIKTOK => 'bg-dark text-white',
            self::PINTEREST => 'bg-danger text-white',
            self::X => 'bg-dark text-white',
            self::THREADS => 'bg-dark text-white',
            self::GOOGLE_BUSINESS => 'bg-success text-white',
            self::BLUESKY => 'bg-info text-white',
        };
    }

    public function supportsPKCE(): bool
    {
        return in_array($this, [self::X, self::TIKTOK, self::PINTEREST], true);
    }
}

