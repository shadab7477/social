<?php

namespace App\Enums;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case SCHEDULED = 'scheduled';
    case QUEUED = 'queued';
    case PUBLISHING = 'publishing';
    case PUBLISHED = 'published';
    case PARTIALLY_PUBLISHED = 'partially_published';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING_APPROVAL => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::SCHEDULED => 'Scheduled',
            self::QUEUED => 'Queued',
            self::PUBLISHING => 'Publishing Now',
            self::PUBLISHED => 'Published',
            self::PARTIALLY_PUBLISHED => 'Partially Published',
            self::FAILED => 'Publishing Failed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PUBLISHED => 'bg-success-subtle text-success border border-success-subtle',
            self::SCHEDULED, self::QUEUED => 'bg-primary-subtle text-primary border border-primary-subtle',
            self::PENDING_APPROVAL => 'bg-warning-subtle text-dark border border-warning-subtle',
            self::APPROVED => 'bg-info-subtle text-info border border-info-subtle',
            self::PUBLISHING => 'bg-primary text-white',
            self::PARTIALLY_PUBLISHED => 'bg-warning-subtle text-warning border border-warning-subtle',
            self::FAILED => 'bg-danger-subtle text-danger border border-danger-subtle',
            default => 'bg-light text-dark border',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::PENDING_APPROVAL, self::FAILED], true);
    }
}

