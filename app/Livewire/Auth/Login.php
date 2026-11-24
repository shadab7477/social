<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = true;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => strtolower(trim($this->email)), 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            $user = Auth::user();
            $workspace = $user->activeWorkspace();

            if ($workspace) {
                session([
                    'active_workspace_id' => $workspace->id,
                    'active_workspace_name' => $workspace->name,
                ]);
            }

            session()->flash('success', "Welcome back, {$user->name}!");

            return $this->redirectIntended(route('dashboard'), navigate: true);
        }

        $this->addError('email', 'These credentials do not match our records.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}

