<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'agent_id',
        'title',
        'metadata',
    ];

    protected static function booted(): void
    {
        static::creating(function ($conversation) {
            if (empty($conversation->uuid)) {
                $conversation->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('created_at');
    }

    public function latestMessage(): ?ConversationMessage
    {
        return $this->messages()->latest()->first();
    }

    public function generateTitle(): void
    {
        if ($this->title) {
            return;
        }

        $firstUserMessage = $this->messages()
            ->where('role', 'user')
            ->first();

        if ($firstUserMessage) {
            // Use first 50 chars of first message as title
            $this->update([
                'title' => Str::limit($firstUserMessage->content, 50, '...'),
            ]);
        }
    }

    public function getTotalTokensAttribute(): int
    {
        return $this->messages()
            ->whereNotNull('metadata->tokens')
            ->get()
            ->sum(fn ($message) => $message->metadata['tokens'] ?? 0);
    }

    public function getTotalCostAttribute(): float
    {
        return $this->messages()
            ->whereNotNull('metadata->cost')
            ->get()
            ->sum(fn ($message) => $message->metadata['cost'] ?? 0);
    }
}
