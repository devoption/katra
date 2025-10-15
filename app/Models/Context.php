<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Context extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'type',
        'content',
        'metadata',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function ($context) {
            if (empty($context->uuid)) {
                $context->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'metadata' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class);
    }

    public function workflowExecutions(): HasMany
    {
        return $this->hasMany(WorkflowExecution::class);
    }
}
