<?php

use App\Models\InstanceConnection;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceAgent;
use App\Models\WorkspaceChat;
use App\Models\WorkspaceChatParticipant;
use App\Support\Chats\WorkspaceAgentManager;
use App\Support\Connections\InstanceConnectionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

test('new workspaces receive a default workspace guide agent', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create();

    $workspace = app(InstanceConnectionManager::class)->createWorkspace($connection, [
        'name' => 'Product Atlas',
    ]);
    $agents = app(WorkspaceAgentManager::class)->agentsFor($workspace);
    $reloadedAgents = app(WorkspaceAgentManager::class)->agentsFor($workspace);

    expect($agents)->toHaveCount(1)
        ->and($agents->first()?->agent_key)->toBe(WorkspaceAgent::KEY_WORKSPACE_GUIDE)
        ->and($agents->first()?->name)->toBe('Workspace Guide')
        ->and($agents->first()?->agent_class)->toBe(WorkspaceAgent::CLASS_WORKSPACE_GUIDE)
        ->and($reloadedAgents)->toHaveCount(1);
});

test('an authenticated user can create a private chat with a workspace agent participant', function () {
    $user = User::factory()->create([
        'first_name' => 'Derek',
        'last_name' => 'Bourgeois',
        'name' => 'Derek Bourgeois',
        'email' => 'derek@katra.io',
    ]);
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'base_url' => 'https://katra.test',
    ]);
    $workspace = Workspace::factory()->for($connection)->create([
        'name' => 'Product Atlas',
    ]);
    $workspaceGuide = app(WorkspaceAgentManager::class)->ensureDefaults($workspace)->first();
    $token = (string) Str::uuid();

    actingAs($user)->withSession([
        'instance_connection.active_id' => $connection->getKey(),
        'chat.create_token' => $token,
    ]);

    post(route('chats.store'), [
        'chat_name' => 'Workspace Guide',
        'chat_kind' => WorkspaceChat::KIND_DIRECT,
        'chat_submission_token' => $token,
        'workspace_agent_id' => $workspaceGuide?->getKey(),
    ])->assertRedirect(route('home'));

    $chat = $workspace->fresh()->chats()->latest('id')->first();

    expect($chat)->not()->toBeNull()
        ->and($chat?->summary)->toContain('Workspace Guide')
        ->and($chat?->has_agent_participant)->toBeTrue()
        ->and($chat?->participants()->count())->toBe(2);

    $agentParticipant = $chat?->participants()
        ->where('participant_type', WorkspaceChatParticipant::TYPE_AGENT)
        ->first();

    expect($agentParticipant)->not()->toBeNull()
        ->and($agentParticipant?->agent?->name)->toBe('Workspace Guide')
        ->and($agentParticipant?->display_name)->toBe('Workspace Guide');
});

test('the shell renders agent-backed chats distinctly from human-only chats', function () {
    $user = User::factory()->create([
        'first_name' => 'Derek',
        'last_name' => 'Bourgeois',
        'name' => 'Derek Bourgeois',
        'email' => 'derek@katra.io',
    ]);
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'name' => 'Katra',
        'base_url' => 'https://katra.test',
    ]);
    $workspace = Workspace::factory()->for($connection)->create([
        'name' => 'Product Atlas',
    ]);
    $workspaceGuide = WorkspaceAgent::factory()->workspaceGuide()->for($workspace)->create();
    $chat = WorkspaceChat::factory()->for($workspace, 'workspace')->direct()->create([
        'name' => 'Workspace Guide',
        'summary' => 'Workspace Guide is a private direct chat inside Product Atlas.',
        'has_agent_participant' => true,
    ]);

    $workspace->forceFill([
        'active_chat_id' => $chat->getKey(),
    ])->save();
    $connection->forceFill([
        'active_workspace_id' => $workspace->getKey(),
    ])->save();

    WorkspaceChatParticipant::factory()->for($chat, 'chat')->create([
        'user_id' => $user->getKey(),
        'participant_type' => WorkspaceChatParticipant::TYPE_HUMAN,
        'participant_key' => 'human:derek@katra.io',
        'display_name' => 'Derek Bourgeois',
    ]);
    WorkspaceChatParticipant::factory()->for($chat, 'chat')->forAgent($workspaceGuide)->create();

    actingAs($user);

    get('/')
        ->assertSuccessful()
        ->assertSee('Workspace Guide')
        ->assertSee('Agent participant')
        ->assertSee('Agent');
});
