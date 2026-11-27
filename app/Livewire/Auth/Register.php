<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $workspace_name = '';
    public string $timezone = 'UTC';
    public string $otpCode = '';
    public bool $otpSent = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'workspace_name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:64'],
        ];
    }

    public function sendOtp(): void
    {
        $this->validate();

        $email = strtolower(trim($this->email));
        $code = (string) random_int(100000, 999999);
        Cache::put("register_otp_{$email}", $code, now()->addMinutes(10));

        try {
            Mail::html(
                "<div style='font-family:sans-serif;max-width:480px;margin:auto;padding:24px;border:1px solid #e2e8f0;border-radius:12px;'>
                    <h2 style='margin:0 0 12px;color:#0f172a;'>Verify Your Email for PostSync</h2>
                    <p style='color:#475569;'>Enter this 6-digit code to finish creating your workspace <strong>{$this->workspace_name}</strong>:</p>
                    <div style='font-size:32px;font-weight:800;letter-spacing:6px;background:#f8fafc;padding:16px;text-align:center;border-radius:8px;color:#0f172a;margin:16px 0;'>{$code}</div>
                </div>",
                function ($msg) use ($email) {
                    $msg->to($email)->subject('Verify Your Email - PostSync');
                }
            );
        } catch (\Throwable $e) {
            Log::warning("Failed sending signup OTP email to {$email}: " . $e->getMessage());
        }

        $this->otpSent = true;
        $this->otpCode = '';
        session()->flash('info', "6-digit verification code sent to {$email}.");
    }

    public function editDetails(): void
    {
        $this->otpSent = false;
        $this->otpCode = '';
    }

    public function verifyAndRegister(WorkspaceManagerService $workspaceManager)
    {
        $this->validate(array_merge($this->rules(), [
            'otpCode' => ['required', 'string', 'digits:6'],
        ]));

        $email = strtolower(trim($this->email));
        $cachedCode = Cache::get("register_otp_{$email}");

        if (!$cachedCode || trim($this->otpCode) !== (string) $cachedCode) {
            $this->addError('otpCode', 'Invalid or expired verification code.');
            return;
        }

        Cache::forget("register_otp_{$email}");

        $user = User::create([
            'name' => $this->name,
            'email' => $email,
            'password' => null,
            'email_verified_at' => now(),
            'timezone' => $this->timezone,
        ]);

        $workspaceManager->createWorkspace(
            $user,
            $this->workspace_name,
            $this->timezone
        );

        Auth::login($user);

        session()->flash('success', "Welcome to PostSync, {$user->name}! Your workspace '{$this->workspace_name}' is ready.");

        if ($inviteToken = session('pending_invite_token')) {
            return $this->redirect(route('invitations.accept', ['token' => $inviteToken]), navigate: true);
        }

        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
