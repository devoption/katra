<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'role',
        'description',
        'model_provider',
        'model_name',
        'system_prompt',
        'creativity_level',
        'is_default',
        'is_active',
        'context_id',
        'credential_id',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function ($agent) {
            if (empty($agent->uuid)) {
                $agent->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'creativity_level' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    public function credential(): BelongsTo
    {
        return $this->belongsTo(Credential::class);
    }

    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class)
            ->withPivot('configuration')
            ->withTimestamps();
    }

    public function workflowSteps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function conversationMessages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class);
    }
}
