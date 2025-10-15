<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Credential extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'type',
        'provider',
        'encrypted_value',
        'metadata',
        'created_by',
    ];

    protected $hidden = [
        'encrypted_value',
    ];

    protected static function booted(): void
    {
        static::creating(function ($credential) {
            if (empty($credential->uuid)) {
                $credential->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn () => Crypt::decryptString($this->encrypted_value),
            set: fn (string $value) => ['encrypted_value' => Crypt::encryptString($value)]
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class)
            ->withTimestamps();
    }
}
