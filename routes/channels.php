<?php

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
