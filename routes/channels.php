<?php

use App\Models\Conversation;
use App\Models\WorkflowExecution;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('workflow-executions.{executionId}', function ($user, string $executionId) {
    return WorkflowExecution::query()
        ->where('id', $executionId)
        ->exists();
});

Broadcast::channel('conversations.{conversationId}', function ($user, int $conversationId) {
    return Conversation::query()
        ->where('id', $conversationId)
        ->where('user_id', $user->id)
        ->exists();
});
