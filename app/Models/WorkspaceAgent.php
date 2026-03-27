<?php

namespace App\Models;

use App\Ai\Agents\WorkspaceGuide;
use Database\Factories\WorkspaceAgentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['workspace_id', 'agent_key', 'name', 'agent_class', 'summary'])]
class WorkspaceAgent extends Model
{
    public const KEY_WORKSPACE_GUIDE = 'workspace-guide';

    public const CLASS_WORKSPACE_GUIDE = WorkspaceGuide::class;

    /** @use HasFactory<WorkspaceAgentFactory> */
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
    public function chatParticipants(): HasMany
    {
        return $this->hasMany(WorkspaceChatParticipant::class, 'workspace_agent_id');
    }
}
