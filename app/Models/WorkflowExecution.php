<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WorkflowExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'workflow_id',
        'workflow_version',
        'status',
        'triggered_by',
        'triggered_by_id',
        'context_id',
        'container_config',
        'input_data',
        'output_data',
        'error_data',
        'metrics',
        'total_cost',
        'started_at',
        'completed_at',
    ];

    protected static function booted(): void
    {
        static::creating(function ($execution) {
            if (empty($execution->uuid)) {
                $execution->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'container_config' => 'array',
            'input_data' => 'array',
            'output_data' => 'array',
            'error_data' => 'array',
            'metrics' => 'array',
            'total_cost' => 'decimal:4',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class);
    }
}
