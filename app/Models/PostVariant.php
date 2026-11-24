<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'social_profile_id',
        'custom_caption',
        'platform_settings',
        'status',
        'scheduled_at',
        'published_at',
        'external_post_id',
        'external_post_url',
        'error_message',
        'retry_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'platform_settings' => 'array',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
            'retry_count' => 'integer',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function socialProfile(): BelongsTo
    {
        return $this->belongsTo(SocialProfile::class);
    }

    public function postMedia(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('sort_order');
    }

    public function mediaAssets(): BelongsToMany
    {
        return $this->belongsToMany(MediaAsset::class, 'post_media')
            ->withPivot(['sort_order', 'custom_alt_text'])
            ->orderBy('sort_order');
    }

    public function publishingRecord(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PublishingRecord::class);
    }

    /**
     * Get the effective caption (custom variant caption if set, otherwise parent post base caption).
     */
    public function getEffectiveCaption(): string
    {
        return !empty($this->custom_caption) ? $this->custom_caption : ($this->post->base_caption ?? '');
    }
}

