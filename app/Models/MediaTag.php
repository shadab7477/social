<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MediaTag extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'workspace_id',
        'name',
    ];

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(MediaAsset::class, 'media_asset_tags');
    }
}

