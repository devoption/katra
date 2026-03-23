<?php

use App\Ai\Agents\WorkspaceGuide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Prompts\AgentPrompt;

uses(RefreshDatabase::class);

test('workspace guide can fake and remember a conversation', function () {
    config()->set('ai.default', 'openai');
    config()->set('ai.providers.openai.key', 'test-openai-key');

    $user = User::factory()->create();

    WorkspaceGuide::fake(function (string $prompt): string {
        return str_contains($prompt, 'tasks, artifacts, and decisions')
            ? 'Treat tasks, artifacts, and decisions as linked nodes instead of disposable transcript branches.'
            : 'Start with one durable room per participant set and keep linked nodes in the context rail.';
    })->preventStrayPrompts();

    $agent = WorkspaceGuide::make()->forUser($user);

    $openingResponse = $agent->prompt('How should Katra structure room context?');
    $conversationId = $agent->currentConversation();

    $followUpAgent = WorkspaceGuide::make()->continueLastConversation($user);
    $followUpResponse = $followUpAgent->prompt('What should happen to tasks, artifacts, and decisions?');

    expect($conversationId)->not->toBeNull()
        ->and($openingResponse->conversationId)->toBe($conversationId)
        ->and($followUpAgent->currentConversation())->toBe($conversationId)
        ->and($followUpResponse->conversationId)->toBe($conversationId)
        ->and($openingResponse->text)->toContain('durable room')
        ->and($followUpResponse->text)->toContain('linked nodes');

    WorkspaceGuide::assertPrompted(function (AgentPrompt $prompt): bool {
        return $prompt->contains('room context');
    });

    WorkspaceGuide::assertPrompted(function (AgentPrompt $prompt): bool {
        return $prompt->contains('tasks, artifacts, and decisions');
    });

    $this->assertDatabaseCount('agent_conversations', 1);
    $this->assertDatabaseCount('agent_conversation_messages', 4);

    $conversation = DB::table('agent_conversations')->first();
    $messages = DB::table('agent_conversation_messages')
        ->where('conversation_id', $conversationId)
        ->orderBy('created_at')
        ->orderBy('id')
        ->pluck('role')
        ->all();

    expect((int) $conversation->user_id)->toBe($user->id)
        ->and($conversation->title)->toContain('durable room')
        ->and($messages)->toBe([
            'user',
            'assistant',
            'user',
            'assistant',
        ]);
});
