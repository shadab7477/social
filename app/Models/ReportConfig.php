<?php

namespace App\Models;

use App\Enums\ReportFrequency;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportConfig extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'workspace_id',
        'client_id',
        'name',
        'frequency',
        'recipients',
        'branding_options',
        'is_active',
        'last_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'frequency' => ReportFrequency::class,
            'recipients' => 'array',
            'branding_options' => 'array',
            'is_active' => 'boolean',
            'last_sent_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}

