<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Livewire\Auth\Login;
use App\Models\Client;
use App\Models\User;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class ClientPortalOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_login_with_gmail_otp_and_has_restricted_access(): void
    {
        $owner = User::factory()->create(['email' => 'owner@agency.com']);
        $workspace = app(WorkspaceManagerService::class)->createWorkspace($owner, 'Agency Hub');

        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name' => 'Sharma Cafe',
            'contact_person' => 'Rohan Sharma',
            'contact_email' => 'rohan.sharma@gmail.com',
            'timezone' => 'Asia/Kolkata',
            'status' => 'active',
        ]);

        // Client signs in using Gmail + 6-digit OTP without prior User record
        $component = Livewire::test(Login::class)
            ->set('email', 'rohan.sharma@gmail.com')
            ->call('sendOtp')
            ->assertSet('otpSent', true);

        $otp = Cache::get('auth_otp_rohan.sharma@gmail.com');
        $this->assertNotEmpty($otp);

        $component
            ->set('otpCode', (string) $otp)
            ->call('verifyOtp')
            ->assertRedirect(route('dashboard'));

        $clientUser = User::where('email', 'rohan.sharma@gmail.com')->first();
        $this->assertNotNull($clientUser);
        $this->assertTrue($clientUser->isClientViewer($workspace));
        $this->assertContains($client->id, $clientUser->assignedClientIds($workspace));

        // Client CAN access allowed portal routes
        $this->actingAs($clientUser)
            ->get(route('dashboard'))
            ->assertOk();
        $this->actingAs($clientUser)
            ->get(route('reports.index'))
            ->assertOk();
        $this->actingAs($clientUser)
            ->get(route('approvals.index'))
            ->assertOk();

        // Client is BLOCKED from agency-only routes (Billing, Team, Clients, Composer, Automations)
        $this->actingAs($clientUser)
            ->get(route('billing.index'))
            ->assertRedirect(route('dashboard'));
        $this->actingAs($clientUser)
            ->get(route('team.index'))
            ->assertRedirect(route('dashboard'));
        $this->actingAs($clientUser)
            ->get(route('clients.index'))
            ->assertRedirect(route('dashboard'));
        $this->actingAs($clientUser)
            ->get(route('composer'))
            ->assertRedirect(route('dashboard'));
        $this->actingAs($clientUser)
            ->get(route('automations.index'))
            ->assertRedirect(route('dashboard'));
    }
}

