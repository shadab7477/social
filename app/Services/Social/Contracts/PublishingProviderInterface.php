<?php

namespace App\Services\Social\Contracts;

use App\Models\SocialProfile;
use App\Services\Social\DTOs\PublishingPayloadDTO;
use App\Services\Social\DTOs\PublishingResultDTO;

interface PublishingProviderInterface
{
    /**
     * Publish content to the external social platform.
     */
    public function publish(SocialProfile $profile, PublishingPayloadDTO $payload): PublishingResultDTO;

    /**
     * Poll status of an asynchronous publication container.
     */
    public function checkPublishStatus(SocialProfile $profile, string $providerPostId): PublishingResultDTO;
}

