<?php

namespace App\Http\Controllers;

use App\Enums\SocialPlatform;
use App\Models\OAuthTransaction;
use App\Models\SocialProfile;
use App\Models\Workspace;
use App\Services\Social\Registry\SocialProviderManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientConnectController extends Controller
{
    public function __construct(
        protected SocialProviderManager $providerManager
    ) {}

    /**
     * Show client connect landing page for the workspace.
     */
    public function show(string $uuid)
    {
        $workspace = Workspace::where('uuid', $uuid)->where('is_active', true)->firstOrFail();
        $connectedProfiles = SocialProfile::withoutGlobalScopes()
            ->where('workspace_id', $workspace->id)
            ->where('is_active', true)
            ->get();

        return view('client-connect.show', compact('workspace', 'connectedProfiles'));
    }

    /**
     * Initiate OAuth connect flow for a client via their shareable workspace link.
     */
    public function connect(Request $request, string $uuid, string $platform)
    {
        $workspace = Workspace::where('uuid', $uuid)->where('is_active', true)->firstOrFail();
        $platformEnum = SocialPlatform::tryFrom($platform);

        if (!$platformEnum) {
            abort(404, "Unsupported social platform: {$platform}");
        }

        // Validate API credentials configured
        $clientId = match ($platformEnum) {
            SocialPlatform::FACEBOOK,
            SocialPlatform::INSTAGRAM => config('services.facebook.client_id', env('META_APP_ID')),
            SocialPlatform::LINKEDIN => config('services.linkedin.client_id', env('LINKEDIN_CLIENT_ID')),
            SocialPlatform::YOUTUBE,
            SocialPlatform::GOOGLE_BUSINESS => config('services.google.client_id', env('GOOGLE_CLIENT_ID')),
            SocialPlatform::PINTEREST => config('services.pinterest.client_id', env('PINTEREST_CLIENT_ID')),
            default => null,
        };

        if (empty($clientId) && !app()->environment('testing')) {
            return redirect()->route('client-connect.show', ['uuid' => $uuid])
                ->with('error', "{$platformEnum->label()} integration is currently being set up by your agency. Please try another platform or contact your agency admin.");
        }

        $codeVerifier = null;
        if ($platformEnum->supportsPKCE()) {
            $codeVerifier = Str::random(64);
        }

        $stateToken = Str::random(64);
        $callbackPlatform = ($platformEnum === SocialPlatform::INSTAGRAM) ? 'facebook' : $platform;
        $redirectUrl = route('oauth.callback', ['platform' => $callbackPlatform]);

        // Create transaction assigned to the workspace owner
        $transaction = OAuthTransaction::create([
            'state_token' => $stateToken,
            'workspace_id' => $workspace->id,
            'user_id' => $workspace->owner_id,
            'platform' => $callbackPlatform,
            'code_verifier' => $codeVerifier,
            'redirect_url' => $redirectUrl,
            'is_consumed' => false,
            'expires_at' => now()->addMinutes(30),
        ]);

        session([
            'client_connect_uuid' => $uuid,
            'client_connect_state' => $stateToken,
            'client_connect_workspace_id' => $workspace->id,
        ]);

        $oauthProvider = $this->providerManager->getOAuthProvider($platformEnum);
        $authUrl = $oauthProvider->getAuthorizationUrl($transaction);

        return redirect()->away($authUrl);
    }

    /**
     * Show success screen after client connects their account.
     */
    public function success(string $uuid)
    {
        $workspace = Workspace::where('uuid', $uuid)->where('is_active', true)->firstOrFail();
        $connectedProfiles = SocialProfile::withoutGlobalScopes()
            ->where('workspace_id', $workspace->id)
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('client-connect.success', compact('workspace', 'connectedProfiles'));
    }
}

