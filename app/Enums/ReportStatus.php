<?php

namespace App\Enums;

enum ReportStatus: string
{
    case GENERATING = 'generating';
    case READY = 'ready';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::GENERATING => 'Generating...',
            self::READY => 'Ready for Download',
            self::FAILED => 'Generation Failed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::GENERATING => 'bg-warning-subtle text-warning border border-warning-subtle',
            self::READY => 'bg-success-subtle text-success border border-success-subtle',
            self::FAILED => 'bg-danger-subtle text-danger border border-danger-subtle',
        };
    }
}

