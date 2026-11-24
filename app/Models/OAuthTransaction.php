<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuthTransaction extends Model
{
    use HasFactory;

    protected $table = 'oauth_transactions';

    protected $fillable = [
        'state_token',
        'workspace_id',
        'user_id',
        'platform',
        'code_verifier',
        'redirect_url',
        'metadata',
        'is_consumed',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_consumed' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function getStateAttribute(): ?string
    {
        return $this->state_token;
    }

    public function consume(): void
    {
        $this->update(['is_consumed' => true]);
    }
}

