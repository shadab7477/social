<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $workspace_name = '';
    public string $timezone = 'UTC';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'workspace_name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:64'],
        ];
    }

    public function register(WorkspaceManagerService $workspaceManager)
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => strtolower(trim($this->email)),
            'password' => Hash::make($this->password),
            'timezone' => $this->timezone,
        ]);

        $workspaceManager->createWorkspace(
            $user,
            $this->workspace_name,
            $this->timezone
        );

        Auth::login($user);

        session()->flash('success', "Welcome to SocialPilotX, {$user->name}! Your workspace '{$this->workspace_name}' is ready.");

        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}

