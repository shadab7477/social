<?php

namespace App\Enums;

enum ApprovalStage: string
{
    case MANAGER_REVIEW = 'manager_review';
    case CLIENT_REVIEW = 'client_review';

    public function label(): string
    {
        return match ($this) {
            self::MANAGER_REVIEW => 'Agency Manager Review',
            self::CLIENT_REVIEW => 'Client Final Review',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::MANAGER_REVIEW => 'bg-indigo-subtle text-indigo border border-indigo-subtle',
            self::CLIENT_REVIEW => 'bg-primary-subtle text-primary border border-primary-subtle',
        };
    }
}

