<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'created_by');
    }

    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class, 'created_by');
    }

    public function contexts(): HasMany
    {
        return $this->hasMany(Context::class, 'created_by');
    }

    public function tools(): HasMany
    {
        return $this->hasMany(Tool::class, 'created_by');
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(Credential::class, 'created_by');
    }

    public function triggers(): HasMany
    {
        return $this->hasMany(Trigger::class, 'created_by');
    }

    public function triggeredExecutions(): HasMany
    {
        return $this->hasMany(WorkflowExecution::class, 'triggered_by_id');
    }
}
