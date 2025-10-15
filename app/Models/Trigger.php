<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Trigger extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'workflow_id',
        'name',
        'type',
        'config',
        'is_active',
        'last_triggered_at',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function ($trigger) {
            if (empty($trigger->uuid)) {
                $trigger->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
