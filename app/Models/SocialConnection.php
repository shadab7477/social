<?php

namespace App\Models;

use App\Enums\ConnectionStatus;
use App\Enums\SocialPlatform;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class SocialConnection extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'workspace_id',
        'platform',
        'account_id',
        'account_name',
        'account_email',
        'avatar_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'refresh_token_expires_at',
        'scopes',
        'status',
        'last_error_message',
        'last_sync_at',
    ];

    protected function casts(): array
    {
        return [
            'platform' => SocialPlatform::class,
            'status' => ConnectionStatus::class,
            'scopes' => 'array',
            'token_expires_at' => 'datetime',
            'refresh_token_expires_at' => 'datetime',
            'last_sync_at' => 'datetime',
        ];
    }

    /**
     * Transparent AES-256-GCM Encryption for Access Token.
     */
    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    /**
     * Transparent AES-256-GCM Encryption for Refresh Token.
     */
    protected function refreshToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    /**
     * Profiles exposed through this connection.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(SocialProfile::class, 'connection_id');
    }

    /**
     * Token events audit trail.
     */
    public function tokenEvents(): HasMany
    {
        return $this->hasMany(SocialTokenEvent::class, 'connection_id');
    }

    public function isExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    public function isPublishable(): bool
    {
        return $this->status->isPublishable() && !$this->isExpired();
    }
}

