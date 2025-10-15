<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiInteractionFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'ai_interaction_id',
        'user_id',
        'rating',
        'thumbs_up',
        'feedback_type',
        'correction_text',
        'explanation',
        'tags',
        'weight',
        'verified_by_admin',
        'verified_by',
        'metadata',
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function ($feedback) {
            if (empty($feedback->uuid)) {
                $feedback->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'thumbs_up' => 'boolean',
            'tags' => 'array',
            'weight' => 'decimal:2',
            'verified_by_admin' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function aiInteraction(): BelongsTo
    {
        return $this->belongsTo(AiInteraction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPositive(): bool
    {
        return $this->thumbs_up === true || $this->rating >= 4;
    }

    public function hasCorrection(): bool
    {
        return ! empty($this->correction_text);
    }
}
