<?php

namespace App\Services\Social\DTOs;

use DateTimeInterface;

readonly class OAuthTokenDTO
{
    public function __construct(
        public string $accessToken,
        public ?string $refreshToken = null,
        public ?DateTimeInterface $expiresAt = null,
        public ?DateTimeInterface $refreshTokenExpiresAt = null,
        public array $scopes = [],
        public ?string $accountId = null,
        public ?string $accountName = null,
        public ?string $accountEmail = null,
        public ?string $avatarUrl = null,
        public array $raw = [],
    ) {}
}

