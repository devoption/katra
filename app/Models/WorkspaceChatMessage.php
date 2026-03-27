<?php

namespace App\Models;

use Database\Factories\WorkspaceChatMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['chat_id', 'sender_type', 'sender_key', 'sender_name', 'body'])]
class WorkspaceChatMessage extends Model
{
    public const SENDER_HUMAN = 'human';

    public const SENDER_AGENT = 'agent';

    public const SENDER_SYSTEM = 'system';

    /** @use HasFactory<WorkspaceChatMessageFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<WorkspaceChat, $this>
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(WorkspaceChat::class, 'chat_id');
    }
}
