<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'workspace_id',
        'name',
        'client_code',
        'contact_person',
        'contact_email',
        'contact_phone',
        'logo_url',
        'timezone',
        'status',
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (empty($client->client_code)) {
                $baseCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $client->name), 0, 4));
                if (strlen($baseCode) < 2) {
                    $baseCode = 'CLNT';
                }
                $code = $baseCode . '-01';
                $counter = 1;
                while (static::where('client_code', $code)->where('workspace_id', $client->workspace_id)->exists()) {
                    $counter++;
                    $code = sprintf('%s-%02d', $baseCode, $counter);
                }
                $client->client_code = $code;
            }
        });
    }

    /**
     * Associated team memberships for this client.
     */
    public function members(): HasMany
    {
        return $this->hasMany(ClientMember::class);
    }

    /**
     * Users assigned to this client.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_members')
            ->withPivot('can_approve')
            ->withTimestamps();
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(SocialProfile::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function reportConfigs(): HasMany
    {
        return $this->hasMany(ReportConfig::class);
    }

    /**
     * Check if a given user is assigned to this client.
     */
    public function isAssigned(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}

