<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MediaAsset extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'uuid',
        'workspace_id',
        'uploader_id',
        'folder_id',
        'file_name',
        'storage_path',
        'disk',
        'mime_type',
        'file_size',
        'media_type',
        'width',
        'height',
        'duration_seconds',
        'aspect_ratio',
        'public_url',
        'thumbnail_url',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration_seconds' => 'float',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MediaAsset $asset) {
            if (empty($asset->uuid)) {
                $asset->uuid = (string) Str::uuid();
            }
        });
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(MediaTag::class, 'media_asset_tags');
    }

    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }

    public function getPublicUrlAttribute(?string $value): string
    {
        if ($this->disk === 'public') {
            return asset('storage/' . ltrim($this->storage_path, '/'));
        }

        return $value ?? asset('storage/' . ltrim($this->storage_path, '/'));
    }

    public function getThumbnailUrlAttribute(?string $value): ?string
    {
        if ($this->isImage()) {
            return $this->public_url;
        }

        return $value;
    }

    public function humanFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

