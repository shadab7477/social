<?php

namespace App\Livewire\Auth;

use App\Enums\WorkspaceRole;
use App\Models\Client;
use App\Models\ClientMember;
use App\Models\User;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Login extends Component
{
    public string $email = '';
    public string $otpCode = '';
    public bool $otpSent = false;
    public bool $remember = true;

    public function sendOtp(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $email = strtolower(trim($this->email));
        $user = User::where('email', $email)->first();
        $clientExists = Client::withoutGlobalScopes()
            ->whereRaw('LOWER(contact_email) = ?', [$email])
            ->exists();

        if (!$user && !$clientExists) {
            $this->addError('email', 'No account or invited client found for this email address.');
            return;
        }

        $code = (string) random_int(100000, 999999);
        Cache::put("auth_otp_{$email}", $code, now()->addMinutes(10));

        try {
            Mail::html(
                "<div style='font-family:sans-serif;max-width:480px;margin:auto;padding:24px;border:1px solid #e2e8f0;border-radius:12px;'>
                    <h2 style='margin:0 0 12px;color:#0f172a;'>Your PostSync Login Code</h2>
                    <p style='color:#475569;'>Use the 6-digit code below to sign in to your account. It expires in 10 minutes.</p>
                    <div style='font-size:32px;font-weight:800;letter-spacing:6px;background:#f8fafc;padding:16px;text-align:center;border-radius:8px;color:#0f172a;margin:16px 0;'>{$code}</div>
                    <p style='font-size:12px;color:#94a3b8;'>If you did not request this code, you can safely ignore this email.</p>
                </div>",
                function ($msg) use ($email) {
                    $msg->to($email)->subject('Your PostSync Login Code');
                }
            );
        } catch (\Throwable $e) {
            Log::warning("Failed sending login OTP email to {$email}: " . $e->getMessage());
        }

        $this->otpSent = true;
        $this->otpCode = '';
        session()->flash('info', "A 6-digit login code has been sent to {$email}.");
    }

    public function changeEmail(): void
    {
        $this->otpSent = false;
        $this->otpCode = '';
    }

    public function verifyOtp()
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'otpCode' => ['required', 'string', 'digits:6'],
        ]);

        $email = strtolower(trim($this->email));
        $cachedCode = Cache::get("auth_otp_{$email}");

        if (!$cachedCode || trim($this->otpCode) !== (string) $cachedCode) {
            $this->addError('otpCode', 'Invalid or expired verification code. Please try again.');
            return;
        }

        Cache::forget("auth_otp_{$email}");

        $user = User::where('email', $email)->first();
        $matchingClients = Client::withoutGlobalScopes()
            ->whereRaw('LOWER(contact_email) = ?', [$email])
            ->get();

        if (!$user && $matchingClients->isNotEmpty()) {
            $firstClient = $matchingClients->first();
            $user = User::create([
                'name' => $firstClient->contact_person ?: $firstClient->name,
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
                'timezone' => $firstClient->timezone ?: 'UTC',
            ]);
        }

        if (!$user) {
            $this->addError('email', 'Account could not be resolved.');
            return;
        }

        // Link client viewer memberships for any matching client contact_email
        foreach ($matchingClients as $client) {
            WorkspaceMember::firstOrCreate(
                [
                    'workspace_id' => $client->workspace_id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => WorkspaceRole::CLIENT_VIEWER,
                    'is_active' => true,
                    'joined_at' => now(),
                ]
            );

            ClientMember::firstOrCreate(
                [
                    'client_id' => $client->id,
                    'user_id' => $user->id,
                ],
                [
                    'can_approve' => true,
                ]
            );
        }

        Auth::login($user, $this->remember);
        session()->regenerate();

        $workspace = $user->activeWorkspace();
        if ($workspace) {
            session([
                'active_workspace_id' => $workspace->id,
                'active_workspace_name' => $workspace->name,
            ]);
        }

        session()->flash('success', "Welcome back, {$user->name}!");

        if ($inviteToken = session('pending_invite_token')) {
            return $this->redirect(route('invitations.accept', ['token' => $inviteToken]), navigate: true);
        }

        return $this->redirectIntended(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
