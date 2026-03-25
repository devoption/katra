<?php

namespace App\Models;

use Database\Factories\InstanceConnectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'name',
    'kind',
    'base_url',
    'session_context',
    'last_authenticated_at',
    'last_used_at',
])]
class InstanceConnection extends Model
{
    public const KIND_CURRENT_INSTANCE = 'current-instance';

    public const KIND_SERVER = 'server';

    /** @use HasFactory<InstanceConnectionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'session_context' => 'encrypted:array',
            'last_authenticated_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function summary(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->kind === self::KIND_CURRENT_INSTANCE
                ? 'This Katra instance'
                : ($this->base_url ?? 'Remote server'),
        );
    }

    protected function isCurrentInstance(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->kind === self::KIND_CURRENT_INSTANCE,
        );
    }

    protected function isAuthenticated(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->last_authenticated_at !== null,
        );
    }
}
