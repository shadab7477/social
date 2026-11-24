<?php

namespace App\Models;

use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'workspace_id',
        'client_id',
        'title',
        'format',
        'date_from',
        'date_to',
        'file_path',
        'file_url',
        'status',
        'branding_options',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'format' => ReportFormat::class,
            'status' => ReportStatus::class,
            'date_from' => 'date',
            'date_to' => 'date',
            'branding_options' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isReady(): bool
    {
        return $this->status === ReportStatus::READY;
    }
}

