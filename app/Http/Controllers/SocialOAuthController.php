<?php

namespace App\Http\Controllers;

use App\Enums\ConnectionStatus;
use App\Enums\SocialPlatform;
use App\Models\OAuthTransaction;
use App\Models\SocialConnection;
use App\Models\SocialTokenEvent;
use App\Services\Social\Registry\SocialProviderManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SocialOAuthController extends Controller
{
    public function __construct(
        protected SocialProviderManager $providerManager
    ) {}

    /**
     * Initiate secure OAuth authorization handshake.
     */
    public function connect(Request $request, string $platform)
    {
        $platformEnum = SocialPlatform::tryFrom($platform);
        if (!$platformEnum) {
            abort(404, "Unsupported social platform: {$platform}");
        }

        $user = $request->user();
        $workspace = app('current_workspace');

        if (!$workspace) {
            abort(400, "No active workspace context found.");
        }

        // Check if member role can connect accounts
        $role = $workspace->getMemberRole($user);
        if (!$role || !$role->canConnectAccounts()) {
            abort(403, "You do not have permission to connect social accounts in this workspace.");
        }

        // Validate platform credentials before redirecting to avoid 3rd party invalid client_id error
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
            $envVarName = match ($platformEnum) {
                SocialPlatform::FACEBOOK, SocialPlatform::INSTAGRAM => 'META_APP_ID and META_APP_SECRET',
                SocialPlatform::LINKEDIN => 'LINKEDIN_CLIENT_ID and LINKEDIN_CLIENT_SECRET',
                SocialPlatform::YOUTUBE, SocialPlatform::GOOGLE_BUSINESS => 'GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET',
                SocialPlatform::PINTEREST => 'PINTEREST_CLIENT_ID and PINTEREST_CLIENT_SECRET',
                default => 'API credentials',
            };

            return redirect()->route('social-accounts.index')
                ->with('error', "{$platformEnum->label()} credentials are not configured yet ({$envVarName} is empty in your .env file). Please update your .env file with your developer credentials, or use the '⚡ Connect Demo / Sandbox Accounts' button to test with pre-configured demo profiles.");
        }

        // Generate PKCE verifier and challenge if platform requires it
        $codeVerifier = null;
        if ($platformEnum->supportsPKCE()) {
            $codeVerifier = Str::random(64);
        }

        $stateToken = Str::random(64);

        // Instagram uses Meta/Facebook Graph OAuth underneath; reuse the facebook callback URI
        // so users don't need a separate redirect URI whitelisted in Meta
        $callbackPlatform = ($platformEnum === SocialPlatform::INSTAGRAM) ? 'facebook' : $platform;
        $redirectUrl = route('oauth.callback', ['platform' => $callbackPlatform]);

        // Save ephemeral transaction record
        $transaction = OAuthTransaction::create([
            'state_token' => $stateToken,
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'platform' => $callbackPlatform,
            'code_verifier' => $codeVerifier,
            'redirect_url' => $redirectUrl,
            'is_consumed' => false,
            'expires_at' => now()->addMinutes(15),
        ]);

        $oauthProvider = $this->providerManager->getOAuthProvider($platformEnum);
        $authUrl = $oauthProvider->getAuthorizationUrl($transaction);

        return redirect()->away($authUrl);
    }

    /**
     * Handle incoming OAuth callback from provider.
     */
    public function callback(Request $request, string $platform)
    {
        // Check for provider-side cancellation or error
        if ($request->has('error')) {
            $errorDesc = $request->get('error_description') ?? $request->get('error');
            return redirect()->route('social-accounts.index')->with('error', "Authorization cancelled: {$errorDesc}");
        }

        $stateToken = $request->get('state');
        $code = $request->get('code');

        if (empty($stateToken) || empty($code)) {
            abort(400, "Invalid OAuth callback response. Missing state or authorization code.");
        }

        // 1. Anti-CSRF & State Validation
        $transaction = OAuthTransaction::where('state_token', $stateToken)
            ->where('is_consumed', false)
            ->first();

        if (!$transaction || $transaction->isExpired()) {
            abort(403, "Invalid or expired OAuth state token. Please initiate the connection again.");
        }

        // 2. Anti-Replay: Immediately consume the transaction state
        $transaction->consume();

        // Check if this was initiated via public Client Connect link
        $isClientConnect = (session('client_connect_state') === $stateToken) || (session('client_connect_workspace_id') === $transaction->workspace_id);
        $currentUser = $request->user();

        if ($isClientConnect) {
            $workspace = $transaction->workspace;
        } else {
            // 3. Prevent Cross-User / Account Hijacking
            if (!$currentUser || $currentUser->id !== $transaction->user_id) {
                abort(403, "OAuth transaction initiator does not match the current authenticated user.");
            }

            // 4. Verify user still has active access to the transaction workspace
            if (!$currentUser->belongsToWorkspace($transaction->workspace_id)) {
                abort(403, "You no longer belong to the workspace that initiated this connection.");
            }
        }

        try {
            $oauthProvider = $this->providerManager->getOAuthProvider($platform);

            // 5. Exchange code for access & refresh tokens
            $tokenDto = $oauthProvider->handleCallback($code, $transaction);

            // 6. Create or update SocialConnection (Tokens are automatically encrypted via Eloquent mutators)
            $connection = SocialConnection::updateOrCreate(
                [
                    'workspace_id' => $transaction->workspace_id,
                    'platform' => $platform,
                    'account_id' => $tokenDto->accountId,
                ],
                [
                    'account_name' => $tokenDto->accountName ?? 'Authorized Account',
                    'account_email' => $tokenDto->accountEmail,
                    'avatar_url' => $tokenDto->avatarUrl,
                    'access_token' => $tokenDto->accessToken,
                    'refresh_token' => $tokenDto->refreshToken,
                    'token_expires_at' => $tokenDto->expiresAt,
                    'refresh_token_expires_at' => $tokenDto->refreshTokenExpiresAt,
                    'scopes' => $tokenDto->scopes,
                    'status' => ConnectionStatus::CONNECTED,
                    'last_sync_at' => now(),
                    'last_error_message' => null,
                ]
            );

            // 7. Audit Log Token Event
            SocialTokenEvent::create([
                'connection_id' => $connection->id,
                'event_type' => 'created',
                'details' => [
                    'ip' => $request->ip(),
                    'scopes' => $tokenDto->scopes,
                    'expires_at' => $tokenDto->expiresAt?->format('Y-m-d H:i:s'),
                ],
            ]);

            // 8. Discover available profiles / pages
            $discoveredProfiles = $oauthProvider->discoverProfiles($tokenDto);

            // 9. Client Connect Flow: Automatically import profiles into client workspace
            if ($isClientConnect) {
                $importedCount = 0;
                foreach ($discoveredProfiles as $profileDto) {
                    $pData = is_array($profileDto) ? $profileDto : $profileDto->toArray();
                    \App\Models\SocialProfile::withoutGlobalScopes()->updateOrCreate(
                        [
                            'workspace_id' => $transaction->workspace_id,
                            'platform' => $pData['platform'],
                            'profile_id' => $pData['profileId'],
                        ],
                        [
                            'connection_id' => $connection->id,
                            'name' => $pData['name'],
                            'username' => $pData['username'] ?? null,
                            'avatar_url' => $pData['avatarUrl'] ?? null,
                            'profile_url' => $pData['profileUrl'] ?? null,
                            'access_token' => $pData['accessToken'] ?? null,
                            'capabilities' => $pData['capabilities'] ?? [],
                            'metadata' => $pData['metadata'] ?? [],
                            'is_active' => true,
                            'publishing_enabled' => true,
                            'last_health_check_at' => now(),
                        ]
                    );
                    $importedCount++;
                }

                $clientUuid = session('client_connect_uuid') ?? $transaction->workspace?->uuid;
                session()->forget(['client_connect_uuid', 'client_connect_state', 'client_connect_workspace_id']);

                return redirect()->route('client-connect.success', ['uuid' => $clientUuid])
                    ->with('success', "🎉 Your '{$connection->account_name}' account and {$importedCount} profile(s) have been successfully linked!");
            }

            // Normal Dashboard Flow: Put discovered profiles in session for the profile selection modal
            session([
                'pending_connection_id' => $connection->id,
                'pending_discovered_profiles' => serialize($discoveredProfiles),
            ]);

            return redirect()->route('social-accounts.index', ['select_profiles' => 1])
                ->with('success', "Account '{$connection->account_name}' connected successfully! Please select the pages or channels you want to import.");

        } catch (Exception $e) {
            if ($isClientConnect && ($clientUuid = session('client_connect_uuid') ?? $transaction->workspace?->uuid)) {
                return redirect()->route('client-connect.show', ['uuid' => $clientUuid])
                    ->with('error', "Failed to complete connection: " . $e->getMessage());
            }

            return redirect()->route('social-accounts.index')->with('error', "Failed to complete connection: " . $e->getMessage());
        }
    }
}

