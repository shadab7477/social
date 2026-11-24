<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'phone',
        'timezone',
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    /**
     * Workspaces this user is a member of.
     */
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_members')
            ->withPivot(['role', 'permissions', 'is_active', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Pivot membership records for this user.
     */
    public function workspaceMembers(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    /**
     * Workspaces owned by this user.
     */
    public function ownedWorkspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    /**
     * Resolve the active workspace for this user.
     */
    public function activeWorkspace(): ?Workspace
    {
        $activeWorkspaceId = session('active_workspace_id');

        if ($activeWorkspaceId) {
            $workspace = $this->workspaces()->where('workspaces.id', $activeWorkspaceId)->first();
            if ($workspace) {
                return $workspace;
            }
        }

        // Fallback to first available active workspace
        $firstWorkspace = $this->workspaces()->where('workspaces.is_active', true)->first();
        if ($firstWorkspace) {
            session(['active_workspace_id' => $firstWorkspace->id]);
            return $firstWorkspace;
        }

        return null;
    }

    /**
     * Check if user is member of a specific workspace.
     */
    public function belongsToWorkspace(int|string $workspaceId): bool
    {
        return $this->workspaces()->where('workspaces.id', $workspaceId)->exists();
    }
}
