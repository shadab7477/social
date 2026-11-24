<?php

namespace App\Models;

use App\Enums\ApprovalActionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalAction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'approval_id',
        'user_id',
        'action',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => ApprovalActionType::class,
            'created_at' => 'datetime',
        ];
    }

    public function approval(): BelongsTo
    {
        return $this->belongsTo(Approval::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

