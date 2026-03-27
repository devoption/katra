<?php

namespace App\Models;

use Database\Factories\WorkspaceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['instance_connection_id', 'active_chat_id', 'name', 'slug', 'summary'])]
class Workspace extends Model
{
    /** @use HasFactory<WorkspaceFactory> */
    use HasFactory;

    protected $table = 'connection_workspaces';

    /**
     * @return BelongsTo<InstanceConnection, $this>
     */
    public function instanceConnection(): BelongsTo
    {
        return $this->belongsTo(InstanceConnection::class);
    }

    /**
     * @return BelongsTo<WorkspaceChat, $this>
     */
    public function activeChat(): BelongsTo
    {
        return $this->belongsTo(WorkspaceChat::class, 'active_chat_id');
    }

    /**
     * @return HasMany<WorkspaceChat, $this>
     */
    public function chats(): HasMany
    {
        return $this->hasMany(WorkspaceChat::class);
    }
}
