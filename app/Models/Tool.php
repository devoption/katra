<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tool extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'type',
        'category',
        'input_schema',
        'output_schema',
        'execution_method',
        'execution_config',
        'requires_credential',
        'is_active',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function ($tool) {
            if (empty($tool->uuid)) {
                $tool->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'input_schema' => 'array',
            'output_schema' => 'array',
            'execution_config' => 'array',
            'requires_credential' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class)
            ->withPivot('configuration')
            ->withTimestamps();
    }

    public function credentials(): BelongsToMany
    {
        return $this->belongsToMany(Credential::class)
            ->withTimestamps();
    }
}
