<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'uuid',
        'workspace_id',
        'client_id',
        'author_id',
        'title',
        'base_caption',
        'destination_link',
        'status',
        'scheduled_at',
        'timezone',
        'requires_approval',
        'revision',
    ];

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'scheduled_at' => 'datetime',
            'requires_approval' => 'boolean',
            'revision' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (empty($post->uuid)) {
                $post->uuid = (string) Str::uuid();
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(PostVariant::class);
    }

    public function approval(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Approval::class);
    }

    public function isPendingApproval(): bool
    {
        return $this->status === PostStatus::PENDING_APPROVAL;
    }

    public function isPublished(): bool
    {
        return $this->status === PostStatus::PUBLISHED;
    }

    public function isScheduled(): bool
    {
        return $this->status === PostStatus::SCHEDULED;
    }
}

