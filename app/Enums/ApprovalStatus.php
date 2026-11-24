<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CHANGES_REQUESTED = 'changes_requested';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::CHANGES_REQUESTED => 'Changes Requested',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning-subtle text-warning border border-warning-subtle',
            self::APPROVED => 'bg-success-subtle text-success border border-success-subtle',
            self::REJECTED => 'bg-danger-subtle text-danger border border-danger-subtle',
            self::CHANGES_REQUESTED => 'bg-orange-subtle text-dark border',
        };
    }
}

