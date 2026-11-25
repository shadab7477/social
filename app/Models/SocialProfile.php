<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class SocialProfile extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'workspace_id',
        'client_id',
        'connection_id',
        'platform',
        'profile_id',
        'name',
        'username',
        'avatar_url',
        'profile_url',
        'access_token',
        'capabilities',
        'metadata',
        'is_active',
        'publishing_enabled',
        'last_health_check_at',
    ];

    protected function casts(): array
    {
        return [
            'platform' => \App\Enums\SocialPlatform::class,
            'capabilities' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
            'publishing_enabled' => 'boolean',
            'last_health_check_at' => 'datetime',
        ];
    }

    /**
     * Optional Page/Profile Token Encryption.
     */
    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(SocialConnection::class, 'connection_id');
    }

    public function getValidAccessToken(): ?string
    {
        return $this->access_token ?? $this->connection?->getValidAccessToken();
    }

    public function analyticsSnapshots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AnalyticsSnapshot::class);
    }

    public function postVariants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PostVariant::class);
    }

    public function conversations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function syncLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AnalyticsSyncLog::class);
    }

    public function supportsCapability(string $capability): bool
    {
        return !empty($this->capabilities[$capability]);
    }
}

