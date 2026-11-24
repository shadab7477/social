<?php

namespace App\Models;

use App\Enums\WorkspaceRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'role',
        'permissions',
        'is_active',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => WorkspaceRole::class,
            'permissions' => 'array',
            'is_active' => 'boolean',
            'joined_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOwner(): bool
    {
        return $this->role === WorkspaceRole::OWNER;
    }

    public function isAdmin(): bool
    {
        return $this->role === WorkspaceRole::ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === WorkspaceRole::MANAGER;
    }

    public function isEditor(): bool
    {
        return $this->role === WorkspaceRole::EDITOR;
    }

    public function isClientViewer(): bool
    {
        return $this->role === WorkspaceRole::CLIENT_VIEWER;
    }

    public function hasPermission(string $permission): bool
    {
        // Owners and Admins have full permissions
        if ($this->isOwner() || $this->isAdmin()) {
            return true;
        }

        if (empty($this->permissions)) {
            return false;
        }

        return !empty($this->permissions[$permission]);
    }
}

