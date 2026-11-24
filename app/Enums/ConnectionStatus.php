<?php

namespace App\Enums;

enum ConnectionStatus: string
{
    case CONNECTED = 'connected';
    case EXPIRED = 'expired';
    case REAUTHORIZATION_REQUIRED = 'reauthorization_required';
    case PERMISSION_MISSING = 'permission_missing';
    case DISCONNECTED = 'disconnected';
    case ERROR = 'error';

    public function label(): string
    {
        return match ($this) {
            self::CONNECTED => 'Connected',
            self::EXPIRED => 'Expired',
            self::REAUTHORIZATION_REQUIRED => 'Reauthorization Required',
            self::PERMISSION_MISSING => 'Permission Missing',
            self::DISCONNECTED => 'Disconnected',
            self::ERROR => 'Error',
        };
    }

    public function isPublishable(): bool
    {
        return $this === self::CONNECTED;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::CONNECTED => 'bg-success-subtle text-success border border-success-subtle',
            self::EXPIRED, self::ERROR => 'bg-danger-subtle text-danger border border-danger-subtle',
            self::REAUTHORIZATION_REQUIRED, self::PERMISSION_MISSING => 'bg-warning-subtle text-warning border border-warning-subtle',
            self::DISCONNECTED => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
        };
    }
}

