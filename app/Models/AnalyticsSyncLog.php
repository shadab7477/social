<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsSyncLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    const CREATED_AT = 'synced_at';

    public function scopeLatest($query, $column = 'synced_at')
    {
        return $query->orderBy($column, 'desc');
    }

    protected $fillable = [
        'social_profile_id',
        'status',
        'records_ingested',
        'error_message',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'records_ingested' => 'integer',
            'synced_at' => 'datetime',
        ];
    }

    public function socialProfile(): BelongsTo
    {
        return $this->belongsTo(SocialProfile::class);
    }
}

