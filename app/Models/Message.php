<?php

namespace App\Models;

use App\Enums\MessageDirection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'external_message_id',
        'direction',
        'sender_name',
        'sender_id',
        'body',
        'attachments',
        'is_hidden',
        'sent_by_user_id',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'direction' => MessageDirection::class,
            'attachments' => 'array',
            'is_hidden' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sentByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }
}

