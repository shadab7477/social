<?php

namespace App\Enums;

enum ConversationStatus: string
{
    case OPEN = 'open';
    case RESOLVED = 'resolved';
    case SPAM = 'spam';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::RESOLVED => 'Resolved',
            self::SPAM => 'Spam',
            self::ARCHIVED => 'Archived',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::OPEN => 'bg-primary-subtle text-primary border border-primary-subtle',
            self::RESOLVED => 'bg-success-subtle text-success border border-success-subtle',
            self::SPAM => 'bg-danger-subtle text-danger border border-danger-subtle',
            self::ARCHIVED => 'bg-light text-muted border',
        };
    }
}

