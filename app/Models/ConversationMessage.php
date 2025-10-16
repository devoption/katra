<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ConversationMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'conversation_id',
        'agent_id',
        'ai_interaction_id',
        'role',
        'content',
        'tool_calls',
        'tool_results',
        'is_streaming',
        'is_complete',
        'metadata',
    ];

    protected static function booted(): void
    {
        static::creating(function ($message) {
            if (empty($message->uuid)) {
                $message->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'tool_calls' => 'array',
            'tool_results' => 'array',
            'is_streaming' => 'boolean',
            'is_complete' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function aiInteraction(): BelongsTo
    {
        return $this->belongsTo(AiInteraction::class);
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    public function isSystem(): bool
    {
        return $this->role === 'system';
    }

    public function isTool(): bool
    {
        return $this->role === 'tool';
    }

    public function hasToolCalls(): bool
    {
        return ! empty($this->tool_calls);
    }

    public function hasToolResults(): bool
    {
        return ! empty($this->tool_results);
    }
}
