<?php

namespace App\Services\Social\Contracts;

use App\Enums\SocialPlatform;

interface SocialProviderInterface
{
    public function getPlatform(): SocialPlatform;

    public function getCapabilities(): array;
}

