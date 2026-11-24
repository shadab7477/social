<?php

namespace App\Enums;

enum WorkspaceRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case EDITOR = 'editor';
    case CLIENT_VIEWER = 'client_viewer';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Agency Owner',
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Content Manager',
            self::EDITOR => 'Content Editor',
            self::CLIENT_VIEWER => 'Client Reviewer',
        };
    }

    public function canManageBilling(): bool
    {
        return $this === self::OWNER;
    }

    public function canManageTeam(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN], true);
    }

    public function canConnectAccounts(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN], true);
    }

    public function canApprovePosts(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN, self::MANAGER, self::CLIENT_VIEWER], true);
    }

    public function canPublishDirectly(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN, self::MANAGER], true);
    }
}

