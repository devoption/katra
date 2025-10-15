<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AiInteraction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'type',
        'status',
        'model_provider',
        'model_name',
        'temperature',
        'max_tokens',
        'system_prompt',
        'prompt',
        'response',
        'messages',
        'tool_calls',
        'tool_results',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'latency_ms',
        'cost_usd',
        'error_message',
        'error_details',
        'user_id',
        'agent_id',
        'workflow_execution_id',
        'workflow_step_id',
        'parent_interaction_id',
        'metadata',
        'include_in_training',
        'quality_score',
    ];

    protected static function booted(): void
    {
        static::creating(function ($interaction) {
            if (empty($interaction->uuid)) {
                $interaction->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:2',
            'cost_usd' => 'decimal:6',
            'quality_score' => 'decimal:2',
            'messages' => 'array',
            'tool_calls' => 'array',
            'tool_results' => 'array',
            'error_details' => 'array',
            'metadata' => 'array',
            'include_in_training' => 'boolean',
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

    public function workflowExecution(): BelongsTo
    {
        return $this->belongsTo(WorkflowExecution::class);
    }

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    public function parentInteraction(): BelongsTo
    {
        return $this->belongsTo(AiInteraction::class, 'parent_interaction_id');
    }

    public function childInteractions(): HasMany
    {
        return $this->hasMany(AiInteraction::class, 'parent_interaction_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(AiInteractionFeedback::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function hasFeedback(): bool
    {
        return $this->feedback()->exists();
    }

    public function averageRating(): ?float
    {
        return $this->feedback()->whereNotNull('rating')->avg('rating');
    }

    public function getFormattedCostAttribute(): string
    {
        if (! $this->cost_usd) {
            return '$0.00';
        }

        return '$'.number_format($this->cost_usd, 4);
    }

    public function getFormattedLatencyAttribute(): string
    {
        if (! $this->latency_ms) {
            return '0ms';
        }

        if ($this->latency_ms >= 1000) {
            return round($this->latency_ms / 1000, 2).'s';
        }

        return $this->latency_ms.'ms';
    }
}
