<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\User;
use App\Services\Clients\ClientService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ClientManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    // Modal Visibility States
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $selectedClientId = null;

    public bool $showAssignModal = false;
    public ?int $assignTargetUserId = null;
    public bool $assignCanApprove = false;

    // Form Fields
    public string $name = '';
    public string $client_code = '';
    public string $contact_person = '';
    public string $contact_email = '';
    public string $contact_phone = '';
    public string $timezone = 'UTC';
    public string $status = 'active';
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'client_code' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'timezone' => ['required', 'string', 'max:64'],
            'status' => ['required', 'in:active,paused,archived'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'client_code', 'contact_person', 'contact_email', 'contact_phone', 'notes', 'selectedClientId', 'isEditing']);
        $this->timezone = app('current_workspace')->timezone ?? 'UTC';
        $this->status = 'active';
        $this->resetValidation();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editClient(int $id): void
    {
        $client = Client::findOrFail($id);
        $this->selectedClientId = $client->id;
        $this->name = $client->name;
        $this->client_code = $client->client_code;
        $this->contact_person = $client->contact_person ?? '';
        $this->contact_email = $client->contact_email ?? '';
        $this->contact_phone = $client->contact_phone ?? '';
        $this->timezone = $client->timezone;
        $this->status = $client->status;
        $this->notes = $client->notes ?? '';
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function saveClient(ClientService $clientService): void
    {
        $this->validate();

        $workspace = app('current_workspace');

        if ($this->isEditing && $this->selectedClientId) {
            $client = Client::findOrFail($this->selectedClientId);
            $clientService->updateClient($client, [
                'name' => $this->name,
                'client_code' => $this->client_code,
                'contact_person' => $this->contact_person,
                'contact_email' => $this->contact_email,
                'contact_phone' => $this->contact_phone,
                'timezone' => $this->timezone,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);
            session()->flash('success', "Client '{$this->name}' updated successfully.");
        } else {
            $clientService->createClient($workspace, [
                'name' => $this->name,
                'client_code' => $this->client_code,
                'contact_person' => $this->contact_person,
                'contact_email' => $this->contact_email,
                'contact_phone' => $this->contact_phone,
                'timezone' => $this->timezone,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);
            session()->flash('success', "Client '{$this->name}' created successfully.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function openAssignModal(int $clientId): void
    {
        $this->selectedClientId = $clientId;
        $this->assignTargetUserId = null;
        $this->assignCanApprove = false;
        $this->showAssignModal = true;
    }

    public function assignMember(ClientService $clientService): void
    {
        $this->validate([
            'assignTargetUserId' => ['required', 'exists:users,id'],
        ]);

        $client = Client::findOrFail($this->selectedClientId);
        $user = User::findOrFail($this->assignTargetUserId);

        $clientService->assignMember($client, $user, $this->assignCanApprove);

        session()->flash('success', "Assigned {$user->name} to {$client->name}.");
        $this->showAssignModal = false;
    }

    public function removeMember(int $clientId, int $userId, ClientService $clientService): void
    {
        $client = Client::findOrFail($clientId);
        $user = User::findOrFail($userId);

        $clientService->removeMember($client, $user);
        session()->flash('success', "Removed {$user->name} from {$client->name}.");
    }

    public function deleteClient(int $id, ClientService $clientService): void
    {
        $client = Client::findOrFail($id);
        $name = $client->name;
        $clientService->deleteClient($client);

        session()->flash('success', "Client '{$name}' was removed.");
    }

    public function render()
    {
        $query = Client::query()->with(['users', 'members']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('client_code', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $clients = $query->latest()->paginate(10);
        $workspaceUsers = app('current_workspace')->users()->where('workspace_members.is_active', true)->get();

        return view('livewire.clients.client-manager', [
            'clients' => $clients,
            'workspaceUsers' => $workspaceUsers,
        ]);
    }
}

