<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublishingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_variant_id',
        'idempotency_key',
        'status',
        'locked_at',
        'locked_by',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function postVariant(): BelongsTo
    {
        return $this->belongsTo(PostVariant::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PublishAttempt::class);
    }
}

