<?php

namespace App\Models;

use Database\Factories\WorkspaceChatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['workspace_id', 'name', 'slug', 'kind', 'visibility', 'summary'])]
class WorkspaceChat extends Model
{
    public const KIND_DIRECT = 'direct';

    public const KIND_GROUP = 'group';

    public const VISIBILITY_PRIVATE = 'private';

    /** @use HasFactory<WorkspaceChatFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Workspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * @return HasMany<WorkspaceChatParticipant, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(WorkspaceChatParticipant::class, 'chat_id');
    }

    /**
     * @return HasMany<WorkspaceChatMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WorkspaceChatMessage::class, 'chat_id');
    }
}
