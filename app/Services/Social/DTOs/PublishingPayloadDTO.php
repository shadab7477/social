<?php

namespace App\Services\Social\DTOs;

readonly class PublishingPayloadDTO
{
    public function __construct(
        public string $caption,
        public array $mediaUrls = [], // Public HTTPS URLs of media files
        public ?string $linkUrl = null,
        public array $platformSettings = [],
        public ?string $idempotencyKey = null,
        public array $mediaPaths = [], // Local filesystem paths when hosted on same server
    ) {}
}

