<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishAttempt extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'publishing_record_id',
        'attempt_number',
        'request_payload',
        'response_status_code',
        'response_body',
        'provider_error_code',
        'provider_error_message',
        'duration_ms',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_body' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function publishingRecord(): BelongsTo
    {
        return $this->belongsTo(PublishingRecord::class);
    }
}

