<?php

namespace App\Enums;

enum ReportFrequency: string
{
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::WEEKLY => 'Weekly Automated Dispatch',
            self::MONTHLY => 'Monthly Automated Dispatch',
        };
    }
}

