<?php

namespace App\Services\Social\Contracts;

use App\Models\OAuthTransaction;
use App\Services\Social\DTOs\OAuthTokenDTO;

interface OAuthProviderInterface
{
    /**
     * Build the official redirect URL for authorization.
     */
    public function getAuthorizationUrl(OAuthTransaction $transaction): string;

    /**
     * Exchange authorization code for token details.
     */
    public function handleCallback(string $code, OAuthTransaction $transaction): OAuthTokenDTO;

    /**
     * Refresh an expired access token using refresh token.
     */
    public function refreshToken(string $refreshToken): OAuthTokenDTO;
}

