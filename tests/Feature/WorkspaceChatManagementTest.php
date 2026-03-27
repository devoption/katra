<?php

use App\Models\InstanceConnection;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceAgent;
use App\Models\WorkspaceChat;
use App\Models\WorkspaceChatMessage;
use App\Models\WorkspaceChatParticipant;
use App\Support\Chats\WorkspaceChatManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

test('active chat resolution creates a default private group chat for the workspace', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create();
    $workspace = Workspace::factory()->for($connection)->create([
        'active_chat_id' => null,
    ]);

    $chat = app(WorkspaceChatManager::class)->activeChatFor($workspace, $user, [
        'name' => $user->name,
        'email' => $user->email,
        'initials' => 'KU',
    ]);

    expect($chat->name)->toBe('General chat')
        ->and($chat->kind)->toBe(WorkspaceChat::KIND_GROUP)
        ->and($chat->visibility)->toBe(WorkspaceChat::VISIBILITY_PRIVATE)
        ->and($workspace->fresh()->active_chat_id)->toBe($chat->getKey())
        ->and($chat->participants()->count())->toBe(1);
});

it('creates private workspace chats from the shell', function (string $kind) {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'base_url' => 'https://katra.test',
    ]);
    $workspace = Workspace::factory()->for($connection)->create();
    $token = (string) Str::uuid();

    $workspace->forceFill([
        'active_chat_id' => null,
    ])->save();

    actingAs($user)->withSession([
        'instance_connection.active_id' => $connection->getKey(),
        'chat.create_token' => $token,
    ]);

    post(route('chats.store'), [
        'chat_name' => $kind === WorkspaceChat::KIND_DIRECT ? 'Morgan' : 'Design Review',
        'chat_kind' => $kind,
        'chat_submission_token' => $token,
    ])->assertRedirect(route('home'));

    $chat = $workspace->fresh()->chats()->latest('id')->first();

    expect($chat)->not()->toBeNull()
        ->and($chat?->kind)->toBe($kind)
        ->and($chat?->visibility)->toBe(WorkspaceChat::VISIBILITY_PRIVATE)
        ->and($chat?->has_agent_participant)->toBeFalse()
        ->and($workspace->fresh()->active_chat_id)->toBe($chat?->getKey())
        ->and($chat?->participants()->count())->toBe(1);
})->with([
    WorkspaceChat::KIND_DIRECT,
    WorkspaceChat::KIND_GROUP,
]);

test('an authenticated user can activate another chat within the same workspace', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create();
    $workspace = Workspace::factory()->for($connection)->create();
    $firstChat = WorkspaceChat::factory()->for($workspace, 'workspace')->create();
    $secondChat = WorkspaceChat::factory()->for($workspace, 'workspace')->direct()->create();

    $workspace->forceFill([
        'active_chat_id' => $firstChat->getKey(),
    ])->save();

    actingAs($user);

    post(route('chats.activate', $secondChat))
        ->assertRedirect(route('home'));

    expect($workspace->fresh()->active_chat_id)->toBe($secondChat->getKey());
});

test('an authenticated user can post a durable message into a workspace chat', function () {
    $user = User::factory()->create([
        'first_name' => 'Derek',
        'last_name' => 'Bourgeois',
        'name' => 'Derek Bourgeois',
        'email' => 'derek@katra.io',
    ]);
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create();
    $workspace = Workspace::factory()->for($connection)->create();
    $chat = WorkspaceChat::factory()->for($workspace, 'workspace')->create([
        'name' => 'Design Review',
    ]);

    actingAs($user);

    post(route('chats.messages.store', $chat), [
        'message_body' => 'Lock the private chat layer before we wire agents into it.',
    ])->assertRedirect(route('home'));

    $message = $chat->fresh()->messages()->first();

    expect($message)->not()->toBeNull()
        ->and($message?->sender_type)->toBe(WorkspaceChatMessage::SENDER_HUMAN)
        ->and($message?->sender_name)->toBe('Derek Bourgeois')
        ->and($message?->body)->toBe('Lock the private chat layer before we wire agents into it.')
        ->and($workspace->fresh()->active_chat_id)->toBe($chat->getKey());
});

test('workspace chats stay private to their parent workspace', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create();
    $workspace = Workspace::factory()->for($connection)->create();
    $otherWorkspace = Workspace::factory()->for($connection)->create();
    $chat = WorkspaceChat::factory()->for($workspace, 'workspace')->create([
        'visibility' => WorkspaceChat::VISIBILITY_PRIVATE,
    ]);

    WorkspaceChatParticipant::factory()->for($chat, 'chat')->create([
        'participant_key' => 'human:derek@katra.io',
        'display_name' => 'Derek Bourgeois',
    ]);

    expect($workspace->chats()->count())->toBe(1)
        ->and($otherWorkspace->chats()->count())->toBe(0)
        ->and($chat->visibility)->toBe(WorkspaceChat::VISIBILITY_PRIVATE);
});

test('an authenticated user cannot create a private chat with a blank name', function (string $chatName) {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'base_url' => 'https://katra.test',
    ]);
    $workspace = Workspace::factory()->for($connection)->create();
    $token = (string) Str::uuid();

    actingAs($user)->withSession([
        'instance_connection.active_id' => $connection->getKey(),
        'chat.create_token' => $token,
    ]);

    post(route('chats.store'), [
        'chat_name' => $chatName,
        'chat_kind' => WorkspaceChat::KIND_GROUP,
        'chat_submission_token' => $token,
    ])->assertSessionHasErrors('chat_name');

    expect($workspace->fresh()->chats()->count())->toBe(0)
        ->and($workspace->fresh()->active_chat_id)->toBeNull();
})->with([
    '',
    '   ',
]);

test('duplicate chat submissions with the same token only create one chat', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'base_url' => 'https://katra.test',
    ]);
    $workspace = Workspace::factory()->for($connection)->create();
    $token = (string) Str::uuid();

    actingAs($user)->withSession([
        'instance_connection.active_id' => $connection->getKey(),
        'chat.create_token' => $token,
    ]);

    post(route('chats.store'), [
        'chat_name' => 'Design Review',
        'chat_kind' => WorkspaceChat::KIND_GROUP,
        'chat_submission_token' => $token,
    ])->assertRedirect(route('home'));

    post(route('chats.store'), [
        'chat_name' => 'Design Review',
        'chat_kind' => WorkspaceChat::KIND_GROUP,
        'chat_submission_token' => $token,
    ])->assertRedirect(route('home'));

    expect($workspace->fresh()->chats()->count())->toBe(1)
        ->and($workspace->fresh()->chats()->first()?->name)->toBe('Design Review');
});

test('a failed agent chat submission can be corrected and resubmitted with the same token', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'base_url' => 'https://katra.test',
    ]);
    $workspace = Workspace::factory()->for($connection)->create();
    $otherWorkspace = Workspace::factory()->for($connection)->create();
    $token = (string) Str::uuid();
    $foreignAgent = WorkspaceAgent::factory()->workspaceGuide()->for($otherWorkspace)->create();

    actingAs($user)->withSession([
        'instance_connection.active_id' => $connection->getKey(),
        'chat.create_token' => $token,
    ]);

    post(route('chats.store'), [
        'chat_name' => 'Workspace Guide',
        'chat_kind' => WorkspaceChat::KIND_DIRECT,
        'chat_submission_token' => $token,
        'workspace_agent_id' => $foreignAgent->getKey(),
    ])->assertSessionHasErrors('workspace_agent_id');

    post(route('chats.store'), [
        'chat_name' => 'Workspace Guide',
        'chat_kind' => WorkspaceChat::KIND_DIRECT,
        'chat_submission_token' => $token,
    ])->assertRedirect(route('home'));

    expect($workspace->fresh()->chats()->count())->toBe(1)
        ->and($workspace->fresh()->chats()->first()?->name)->toBe('Workspace Guide');
});
