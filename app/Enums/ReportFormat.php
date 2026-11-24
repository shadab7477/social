<?php

namespace App\Enums;

enum ReportFormat: string
{
    case PDF = 'pdf';
    case CSV = 'csv';

    public function label(): string
    {
        return match ($this) {
            self::PDF => 'PDF Executive Summary',
            self::CSV => 'CSV Raw Data Export',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PDF => 'bi-filetype-pdf text-danger',
            self::CSV => 'bi-filetype-csv text-success',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PDF => 'bg-danger-subtle text-danger border border-danger-subtle',
            self::CSV => 'bg-success-subtle text-success border border-success-subtle',
        };
    }
}

