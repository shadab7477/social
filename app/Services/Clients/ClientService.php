<?php

namespace App\Services\Clients;

use App\Models\Client;
use App\Models\ClientMember;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientService
{
    /**
     * Create a new client within the specified workspace.
     */
    public function createClient(Workspace $workspace, array $data): Client
    {
        return DB::transaction(function () use ($workspace, $data) {
            $clientCode = !empty($data['client_code']) ? strtoupper(trim($data['client_code'])) : null;

            if ($clientCode && Client::withTrashed()->where('workspace_id', $workspace->id)->where('client_code', $clientCode)->exists()) {
                throw ValidationException::withMessages([
                    'client_code' => "The client code '{$clientCode}' is already taken in this workspace.",
                ]);
            }

            return Client::create([
                'workspace_id' => $workspace->id,
                'name' => trim($data['name']),
                'client_code' => $clientCode,
                'contact_person' => $data['contact_person'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'timezone' => $data['timezone'] ?? $workspace->timezone,
                'status' => $data['status'] ?? 'active',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Update client attributes.
     */
    public function updateClient(Client $client, array $data): Client
    {
        if (isset($data['client_code'])) {
            $code = strtoupper(trim($data['client_code']));
            $exists = Client::where('workspace_id', $client->workspace_id)
                ->where('client_code', $code)
                ->where('id', '!=', $client->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'client_code' => "The client code '{$code}' is already in use by another client.",
                ]);
            }
            $data['client_code'] = $code;
        }

        $client->update($data);
        return $client->fresh();
    }

    /**
     * Assign a workspace user to a specific client.
     */
    public function assignMember(Client $client, User $user, bool $canApprove = false): ClientMember
    {
        return ClientMember::updateOrCreate(
            [
                'client_id' => $client->id,
                'user_id' => $user->id,
            ],
            [
                'can_approve' => $canApprove,
            ]
        );
    }

    /**
     * Remove a user from a client.
     */
    public function removeMember(Client $client, User $user): bool
    {
        return (bool) ClientMember::where('client_id', $client->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * Soft delete a client.
     */
    public function deleteClient(Client $client): bool
    {
        return (bool) $client->delete();
    }
}

