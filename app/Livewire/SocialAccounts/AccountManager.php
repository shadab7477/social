<?php

namespace App\Livewire\SocialAccounts;

use App\Enums\ConnectionStatus;
use App\Enums\SocialPlatform;
use App\Models\Client;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Services\Social\DTOs\DiscoveredProfileDTO;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AccountManager extends Component
{
    // Profile Selection Modal State
    public bool $showProfileModal = false;
    public ?int $pendingConnectionId = null;
    public array $discoveredProfiles = [];
    public array $selectedProfileIds = [];
    public ?int $assignClientId = null;

    public function mount()
    {
        if (session()->has('pending_discovered_profiles')) {
            $this->pendingConnectionId = session('pending_connection_id');
            $raw = session('pending_discovered_profiles');
            $dtos = is_string($raw) ? unserialize($raw) : $raw;

            $this->discoveredProfiles = [];
            $this->selectedProfileIds = [];

            if (is_array($dtos)) {
                foreach ($dtos as $dto) {
                    $array = $dto instanceof DiscoveredProfileDTO ? $dto->toArray() : (array)$dto;
                    $this->discoveredProfiles[] = $array;
                    $this->selectedProfileIds[] = $array['profileId'];
                }
            }

            $this->showProfileModal = true;
        }
    }

    public function importSelectedProfiles()
    {
        $connection = SocialConnection::findOrFail($this->pendingConnectionId);
        $workspace = app('current_workspace');

        $importedCount = 0;
        foreach ($this->discoveredProfiles as $item) {
            if (in_array($item['profileId'], $this->selectedProfileIds, true)) {
                SocialProfile::updateOrCreate(
                    [
                        'workspace_id' => $workspace->id,
                        'platform' => $item['platform'],
                        'profile_id' => $item['profileId'],
                    ],
                    [
                        'connection_id' => $connection->id,
                        'client_id' => $this->assignClientId,
                        'name' => $item['name'],
                        'username' => $item['username'] ?? null,
                        'avatar_url' => $item['avatarUrl'] ?? null,
                        'profile_url' => $item['profileUrl'] ?? null,
                        'access_token' => $item['accessToken'] ?? null,
                        'capabilities' => $item['capabilities'] ?? [],
                        'metadata' => $item['metadata'] ?? [],
                        'is_active' => true,
                        'publishing_enabled' => true,
                        'last_health_check_at' => now(),
                    ]
                );
                $importedCount++;
            }
        }

        // Clean up session
        session()->forget(['pending_discovered_profiles', 'pending_connection_id']);
        $this->showProfileModal = false;

        session()->flash('success', "Successfully imported {$importedCount} profile(s)!");
    }

    public function disconnectConnection(int $connectionId)
    {
        $connection = SocialConnection::findOrFail($connectionId);
        $name = $connection->account_name;

        // Disconnect connection and disable publishing for all child profiles
        $connection->update(['status' => ConnectionStatus::DISCONNECTED]);
        $connection->profiles()->update(['publishing_enabled' => false, 'is_active' => false]);

        session()->flash('success', "Disconnected {$name}. Publishing has been disabled for its profiles.");
    }

    public function deleteConnection(int $connectionId)
    {
        $connection = SocialConnection::findOrFail($connectionId);
        $name = $connection->account_name;
        $connection->profiles()->delete();
        $connection->delete();

        session()->flash('success', "Removed connection '{$name}'.");
    }

    public function discoverForConnection(int $connectionId)
    {
        $connection = SocialConnection::findOrFail($connectionId);
        $platEnum = $connection->platform instanceof SocialPlatform
            ? $connection->platform
            : SocialPlatform::from((string) $connection->platform);

        try {
            $provider = app(\App\Services\Social\Registry\SocialProviderManager::class)->getOAuthProvider($platEnum);
            $tokenDto = new \App\Services\Social\DTOs\OAuthTokenDTO(
                accessToken: $connection->access_token,
                refreshToken: $connection->refresh_token,
                expiresAt: $connection->token_expires_at,
                scopes: $connection->scopes ?? [],
                accountId: $connection->account_id,
                accountName: $connection->account_name,
                accountEmail: $connection->account_email,
            );

            $dtos = $provider->discoverProfiles($tokenDto);

            if (empty($dtos)) {
                session()->flash('error', "No {$platEnum->label()} pages, channels, or locations were found for '{$connection->account_name}'.");
                return;
            }

            $this->pendingConnectionId = $connection->id;
            $this->discoveredProfiles = [];
            $this->selectedProfileIds = [];

            foreach ($dtos as $dto) {
                $arr = $dto instanceof DiscoveredProfileDTO ? $dto->toArray() : (array) $dto;
                $this->discoveredProfiles[] = $arr;
                $this->selectedProfileIds[] = $arr['profileId'];
            }

            $this->showProfileModal = true;
        } catch (\Throwable $e) {
            session()->flash('error', "Could not fetch profiles for '{$connection->account_name}': " . $e->getMessage());
        }
    }

    public function toggleProfilePublishing(int $profileId)
    {
        $profile = SocialProfile::findOrFail($profileId);
        $profile->update([
            'publishing_enabled' => !$profile->publishing_enabled,
        ]);

        $status = $profile->publishing_enabled ? 'enabled' : 'disabled';
        session()->flash('success', "Publishing {$status} for '{$profile->name}'.");
    }

    public function deleteProfile(int $profileId)
    {
        $profile = SocialProfile::findOrFail($profileId);
        $name = $profile->name;
        $profile->delete();

        session()->flash('success', "Profile '{$name}' removed.");
    }

    /**
     * Instantly connect demo Facebook, Instagram & LinkedIn profiles for local testing.
     */
    public function connectDemoProfiles()
    {
        $workspace = app('current_workspace');

        // Create or find Demo Connection
        $connection = SocialConnection::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'platform' => SocialPlatform::FACEBOOK,
                'account_id' => 'demo_meta_account_101',
            ],
            [
                'account_name' => $workspace->name . ' (Demo Account)',
                'account_email' => 'demo@example.com',
                'access_token' => 'mock_access_token_demo_' . time(),
                'status' => ConnectionStatus::CONNECTED,
                'scopes' => ['pages_show_list', 'pages_manage_posts', 'instagram_basic', 'instagram_content_publish'],
                'last_sync_at' => now(),
            ]
        );

        // 1. Demo Facebook Page
        SocialProfile::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'platform' => SocialPlatform::FACEBOOK,
                'profile_id' => 'fb_page_demo_101',
            ],
            [
                'connection_id' => $connection->id,
                'name' => $workspace->name . ' Facebook Page',
                'username' => 'freelancers_it',
                'avatar_url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=120&auto=format&fit=crop&q=60',
                'profile_url' => 'https://facebook.com',
                'access_token' => 'mock_page_token_demo_' . time(),
                'capabilities' => [
                    'text_limit' => 63206,
                    'supports_text' => true,
                    'supports_images' => true,
                    'max_images' => 10,
                    'supports_video' => true,
                    'supports_reels' => true,
                    'supports_stories' => true,
                    'supports_links' => true,
                    'supports_scheduling' => true,
                ],
                'is_active' => true,
                'publishing_enabled' => true,
                'last_health_check_at' => now(),
            ]
        );

        // 2. Demo Instagram Account
        SocialProfile::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'platform' => SocialPlatform::INSTAGRAM,
                'profile_id' => 'ig_profile_demo_102',
            ],
            [
                'connection_id' => $connection->id,
                'name' => $workspace->name . ' Instagram',
                'username' => 'freelancers_it_official',
                'avatar_url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=120&auto=format&fit=crop&q=60',
                'profile_url' => 'https://instagram.com/freelancers_it_official',
                'access_token' => 'mock_page_token_demo_' . time(),
                'capabilities' => [
                    'text_limit' => 2200,
                    'supports_text' => false,
                    'supports_images' => true,
                    'max_images' => 10,
                    'supports_carousel' => true,
                    'supports_video' => true,
                    'supports_reels' => true,
                    'supports_scheduling' => true,
                ],
                'is_active' => true,
                'publishing_enabled' => true,
                'last_health_check_at' => now(),
            ]
        );

        // 3. Demo LinkedIn Page
        SocialProfile::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'platform' => SocialPlatform::LINKEDIN,
                'profile_id' => 'urn:li:organization:demo_company_103',
            ],
            [
                'connection_id' => $connection->id,
                'name' => $workspace->name . ' LinkedIn',
                'username' => 'freelancers-it-company',
                'avatar_url' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=120&auto=format&fit=crop&q=60',
                'profile_url' => 'https://linkedin.com',
                'access_token' => 'mock_page_token_demo_' . time(),
                'capabilities' => [
                    'text_limit' => 3000,
                    'supports_text' => true,
                    'supports_images' => true,
                    'max_images' => 9,
                    'supports_video' => true,
                    'supports_links' => true,
                    'supports_scheduling' => true,
                ],
                'is_active' => true,
                'publishing_enabled' => true,
                'last_health_check_at' => now(),
            ]
        );

        session()->flash('success', 'Demo accounts connected successfully! You can now test composing, scheduling, and publishing posts across Facebook, Instagram, and LinkedIn.');
    }

    public function render()
    {
        $workspace = app('current_workspace');
        $connections = $workspace->hasMany(SocialConnection::class)->with('profiles.client')->get();
        $clients = Client::all();

        // Calculate profile counts per platform
        $connectedProfiles = SocialProfile::all();
        $platformCounts = [];
        foreach ($connectedProfiles as $p) {
            $platKey = $p->platform instanceof SocialPlatform ? $p->platform->value : (string) $p->platform;
            $platformCounts[$platKey] = ($platformCounts[$platKey] ?? 0) + 1;
        }

        $allPlatforms = SocialPlatform::cases();

        return view('livewire.social-accounts.account-manager', [
            'connections' => $connections,
            'clients' => $clients,
            'allPlatforms' => $allPlatforms,
            'platformCounts' => $platformCounts,
        ]);
    }
}
