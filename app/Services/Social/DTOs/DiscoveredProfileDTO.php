<?php

namespace App\Services\Social\DTOs;

readonly class DiscoveredProfileDTO
{
    public function __construct(
        public string $platform,
        public string $profileId,
        public string $name,
        public ?string $username = null,
        public ?string $avatarUrl = null,
        public ?string $profileUrl = null,
        public ?string $accessToken = null,
        public array $capabilities = [],
        public array $metadata = [],
    ) {}

    public function toArray(): array
    {
        return [
            'platform' => $this->platform,
            'profileId' => $this->profileId,
            'name' => $this->name,
            'username' => $this->username,
            'avatarUrl' => $this->avatarUrl,
            'profileUrl' => $this->profileUrl,
            'accessToken' => $this->accessToken,
            'capabilities' => $this->capabilities,
            'metadata' => $this->metadata,
        ];
    }
}
