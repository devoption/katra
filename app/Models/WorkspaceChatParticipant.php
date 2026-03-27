<?php

namespace App\Models;

use Database\Factories\WorkspaceChatParticipantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['chat_id', 'user_id', 'workspace_agent_id', 'participant_type', 'participant_key', 'display_name'])]
class WorkspaceChatParticipant extends Model
{
    public const TYPE_HUMAN = 'human';

    public const TYPE_AGENT = 'agent';

    /** @use HasFactory<WorkspaceChatParticipantFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<WorkspaceChat, $this>
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(WorkspaceChat::class, 'chat_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<WorkspaceAgent, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(WorkspaceAgent::class, 'workspace_agent_id');
    }
}
