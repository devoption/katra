<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'workflow_execution_id',
        'agent_id',
        'step_name',
        'step_definition',
        'status',
        'order',
        'input_data',
        'output_data',
        'logs',
        'error_data',
        'container_id',
        'cost',
        'started_at',
        'completed_at',
    ];

    protected static function booted(): void
    {
        static::creating(function ($step) {
            if (empty($step->uuid)) {
                $step->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'step_definition' => 'array',
            'input_data' => 'array',
            'output_data' => 'array',
            'error_data' => 'array',
            'cost' => 'decimal:4',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function execution(): BelongsTo
    {
        return $this->belongsTo(WorkflowExecution::class, 'workflow_execution_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
