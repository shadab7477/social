<?php

namespace App\Services\Social\Contracts;

use App\Services\Social\DTOs\DiscoveredProfileDTO;
use App\Services\Social\DTOs\OAuthTokenDTO;

interface ProfileDiscoveryInterface
{
    /**
     * Discover all accessible pages, channels, or profiles for the authorized account.
     *
     * @return array<DiscoveredProfileDTO>
     */
    public function discoverProfiles(OAuthTokenDTO $token): array;
}

