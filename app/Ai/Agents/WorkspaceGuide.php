<?php

namespace App\Ai\Agents;

use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;
use Stringable;

class WorkspaceGuide implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are Katra's workspace guide.

Help shape durable, graph-native collaboration spaces. Favor concise guidance that keeps rooms, chats, agents, tasks, artifacts, and decisions connected without turning the product into a disposable chat transcript.
PROMPT;
    }
}
