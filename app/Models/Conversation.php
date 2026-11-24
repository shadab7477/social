<?php

namespace App\Models;

use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'workspace_id',
        'social_profile_id',
        'external_conversation_id',
        'type',
        'sender_name',
        'sender_avatar',
        'sender_profile_id',
        'snippet',
        'status',
        'is_unread',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ConversationType::class,
            'status' => ConversationStatus::class,
            'is_unread' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    public function socialProfile(): BelongsTo
    {
        return $this->belongsTo(SocialProfile::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->oldest('sent_at');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ConversationAssignment::class)->latest();
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(ConversationAssignment::class)->latestOfMany();
    }
}

