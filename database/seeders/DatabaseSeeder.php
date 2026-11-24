<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@socialpilotx.com'],
            [
                'name' => 'Freelancers IT',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
            ]
        );

        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($user, 'Freelancers IT Agency');

        Client::updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'name' => 'Acme Corporation',
            ],
            [
                'client_code' => 'ACM-001',
                'notes' => 'Flagship enterprise client for multi-platform social management.',
            ]
        );
    }
}
