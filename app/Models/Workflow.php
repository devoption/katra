<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Workflow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'version',
        'definition',
        'execution_mode',
        'is_active',
        'context_id',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function ($workflow) {
            if (empty($workflow->uuid)) {
                $workflow->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'definition' => 'array',
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

    public function executions(): HasMany
    {
        return $this->hasMany(WorkflowExecution::class);
    }

    public function triggers(): HasMany
    {
        return $this->hasMany(Trigger::class);
    }
}
