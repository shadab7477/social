<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsSnapshot extends Model
{
    use HasFactory, BelongsToTenant;

    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'social_profile_id',
        'post_variant_id',
        'metric_date',
        'metric_name',
        'metric_value',
        'period',
        'raw_payload',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'metric_value' => 'decimal:2',
            'raw_payload' => 'array',
            'fetched_at' => 'datetime',
        ];
    }

    public function socialProfile(): BelongsTo
    {
        return $this->belongsTo(SocialProfile::class);
    }

    public function postVariant(): BelongsTo
    {
        return $this->belongsTo(PostVariant::class);
    }
}

