<?php

namespace App\Services\Social\DTOs;

readonly class PublishingResultDTO
{
    public function __construct(
        public bool $success,
        public ?string $providerPostId = null,
        public ?string $postUrl = null,
        public ?string $errorMessage = null,
        public ?string $errorCode = null,
        public bool $retriable = false,
        public array $rawResponse = [],
    ) {}

    public static function successful(string $postId, ?string $postUrl = null, array $raw = []): self
    {
        return new self(
            success: true,
            providerPostId: $postId,
            postUrl: $postUrl,
            rawResponse: $raw,
        );
    }

    public static function failed(string $message, ?string $code = null, bool $retriable = false, array $raw = []): self
    {
        return new self(
            success: false,
            errorMessage: $message,
            errorCode: $code,
            retriable: $retriable,
            rawResponse: $raw,
        );
    }
}

