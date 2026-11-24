<?php

namespace App\Enums;

enum ApprovalActionType: string
{
    case SUBMITTED = 'submitted';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case REQUESTED_CHANGES = 'requested_changes';

    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Submitted for Approval',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::REQUESTED_CHANGES => 'Requested Changes',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SUBMITTED => 'bi-send',
            self::APPROVED => 'bi-check-circle-fill text-success',
            self::REJECTED => 'bi-x-circle-fill text-danger',
            self::REQUESTED_CHANGES => 'bi-arrow-counterclockwise text-warning',
        };
    }
}

