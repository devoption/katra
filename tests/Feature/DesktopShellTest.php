<?php

use App\Models\InstanceConnection;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceChat;
use App\Models\WorkspaceChatMessage;
use App\Models\WorkspaceChatParticipant;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function configureDesktopShell(): void
{
    config()->set('app.name', 'Katra');
    config()->set('pennant.default', 'array');
    config()->set('surreal.autostart', false);
    config()->set('surreal.host', '127.0.0.1');
    config()->set('surreal.port', 18999);
    config()->set('surreal.endpoint', 'ws://127.0.0.1:18999');
    config()->set('surreal.binary', 'surreal-missing-binary-for-desktop-shell-test');
}

function desktopShellUser(): User
{
    return User::factory()->create([
        'first_name' => 'Derek',
        'last_name' => 'Bourgeois',
        'name' => 'Derek Bourgeois',
        'email' => 'derek@katra.io',
    ]);
}

test('the desktop shell exposes the connection-aware workspace shell', function () {
    configureDesktopShell();

    $user = desktopShellUser();
    $currentConnection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'name' => 'Katra',
        'base_url' => 'https://katra.test',
        'last_authenticated_at' => now(),
        'last_used_at' => now(),
    ]);
    $activeWorkspace = Workspace::factory()->for($currentConnection)->create([
        'name' => 'Product Atlas',
        'slug' => 'product-atlas',
        'summary' => 'Product Atlas is a workspace on this instance for conversations, tasks, and linked work.',
    ]);
    Workspace::factory()->for($currentConnection)->create([
        'name' => 'General',
        'slug' => 'general',
    ]);
    $activeChat = WorkspaceChat::factory()->for($activeWorkspace, 'workspace')->create([
        'name' => 'Design Review',
        'slug' => 'design-review',
        'kind' => WorkspaceChat::KIND_GROUP,
        'visibility' => WorkspaceChat::VISIBILITY_PRIVATE,
    ]);
    WorkspaceChat::factory()->for($activeWorkspace, 'workspace')->direct()->create([
        'name' => 'Founder Sync',
        'slug' => 'founder-sync',
        'visibility' => WorkspaceChat::VISIBILITY_PRIVATE,
    ]);
    WorkspaceChatParticipant::factory()->for($activeChat, 'chat')->create([
        'user_id' => $user->getKey(),
        'participant_type' => WorkspaceChatParticipant::TYPE_HUMAN,
        'participant_key' => 'human:derek@katra.io',
        'display_name' => 'Derek Bourgeois',
    ]);
    WorkspaceChatParticipant::factory()->for($activeChat, 'chat')->create([
        'user_id' => null,
        'participant_type' => WorkspaceChatParticipant::TYPE_HUMAN,
        'participant_key' => 'human:morgan@katra.io',
        'display_name' => 'Morgan Hale',
    ]);
    WorkspaceChatMessage::factory()->for($activeChat, 'chat')->create([
        'sender_type' => WorkspaceChatMessage::SENDER_HUMAN,
        'sender_key' => 'human:derek@katra.io',
        'sender_name' => 'Derek Bourgeois',
        'body' => 'Lock the private chat layer before we wire agents into it.',
    ]);
    WorkspaceChatMessage::factory()->for($activeChat, 'chat')->create([
        'sender_type' => WorkspaceChatMessage::SENDER_HUMAN,
        'sender_key' => 'human:morgan@katra.io',
        'sender_name' => 'Morgan Hale',
        'body' => 'Agreed. The workspace should own chats, not the other way around.',
    ]);

    $currentConnection->forceFill([
        'active_workspace_id' => $activeWorkspace->getKey(),
    ])->save();
    $activeWorkspace->forceFill([
        'active_chat_id' => $activeChat->getKey(),
    ])->save();

    InstanceConnection::factory()->for($user)->create([
        'name' => 'Relay Cloud',
        'kind' => InstanceConnection::KIND_SERVER,
        'base_url' => 'https://relay.devoption.test',
        'last_authenticated_at' => now(),
        'last_used_at' => now()->subMinute(),
    ]);

    actingAs($user);

    get('/')
        ->assertSuccessful()
        ->assertSee('Katra')
        ->assertSee('Workspaces')
        ->assertSee('Rooms')
        ->assertSee('Chats')
        ->assertSee('Create workspace')
        ->assertSee('Create room')
        ->assertSee('Create chat')
        ->assertSee('Product Atlas')
        ->assertSee('General')
        ->assertSee('Design Review')
        ->assertSee('Founder Sync')
        ->assertSee('Morgan Hale')
        ->assertSee('# general')
        ->assertSee('Connections')
        ->assertSee('Add a server')
        ->assertSee('Connection name')
        ->assertSee('Add connection')
        ->assertSee('Katra')
        ->assertSee('Relay Cloud')
        ->assertSee('Edit connection')
        ->assertSee('relay.devoption.test')
        ->assertSee('Collapse sidebar')
        ->assertSee('Expand sidebar')
        ->assertSee('Search conversations, people, and nodes')
        ->assertSee('People and agents')
        ->assertSee('Workspace')
        ->assertSee('Open context panel')
        ->assertSee('Close context panel')
        ->assertSee('Pin context panel')
        ->assertSee('Resize context panel')
        ->assertSee('Manage people')
        ->assertSee('Nodes')
        ->assertSee('Open')
        ->assertSee('Closed')
        ->assertSee('In review')
        ->assertSee('Send message')
        ->assertSee('Message Design Review')
        ->assertSee('Lock the private chat layer before we wire agents into it.')
        ->assertSee('Agreed. The workspace should own chats, not the other way around.')
        ->assertSee('Product Atlas is a workspace on this instance for conversations, tasks, and linked work.')
        ->assertSee('Derek Bourgeois')
        ->assertSee('derek@katra.io')
        ->assertSee('Profile settings')
        ->assertSee('Workspace settings')
        ->assertSee('Administration')
        ->assertSee('Light')
        ->assertSee('Dark')
        ->assertSee('System')
        ->assertSee('Log out')
        ->assertDontSee('desktop mvp preview')
        ->assertDontSee('composer native:dev')
        ->assertDontSee('Surreal Foundation')
        ->assertDontSee('Runtime')
        ->assertDontSee('Binary')
        ->assertDontSee('Endpoint')
        ->assertDontSee('single active session')
        ->assertDontSee('Type')
        ->assertDontSee('First note')
        ->assertDontSee('Views')
        ->assertDontSee('Workspace navigation pilot')
        ->assertDontSee('Favorites')
        ->assertDontSee('Message input will live here.');
});

test('the desktop shell falls back to default feature flags before the Pennant table exists', function () {
    config()->set('pennant.default', 'database');
    config()->set('pennant.stores.database.connection', 'pennant_fallback');
    config()->set('database.connections.pennant_fallback', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);
    config()->set('surreal.autostart', false);
    config()->set('surreal.host', '127.0.0.1');
    config()->set('surreal.port', 18999);
    config()->set('surreal.endpoint', 'ws://127.0.0.1:18999');
    config()->set('surreal.binary', 'surreal-missing-binary-for-desktop-shell-test');

    actingAs(desktopShellUser());

    get('/')
        ->assertSuccessful()
        ->assertSee('Katra')
        ->assertSee('General')
        ->assertSee('Workspaces');
});

test('the desktop shell can render a saved server connection as the active connection', function () {
    configureDesktopShell();

    $user = desktopShellUser();
    $connection = InstanceConnection::factory()->for($user)->create([
        'name' => 'Relay Cloud',
        'kind' => InstanceConnection::KIND_SERVER,
        'base_url' => 'https://relay.devoption.test',
        'last_authenticated_at' => now(),
        'last_used_at' => now(),
        'session_context' => [
            'user' => [
                'name' => 'Relay Operator',
                'email' => 'ops@relay.devoption.test',
            ],
        ],
    ]);
    $workspace = Workspace::factory()->for($connection)->create([
        'name' => 'Relay Launch',
        'slug' => 'relay-launch',
        'summary' => 'Relay Launch is the active workspace on Relay Cloud for shared orchestration, worker presence, and linked team context.',
    ]);
    $activeChat = WorkspaceChat::factory()->for($workspace, 'workspace')->create([
        'name' => 'Ops Briefing',
        'slug' => 'ops-briefing',
        'kind' => WorkspaceChat::KIND_GROUP,
        'visibility' => WorkspaceChat::VISIBILITY_PRIVATE,
    ]);
    WorkspaceChatParticipant::factory()->for($activeChat, 'chat')->create([
        'user_id' => null,
        'participant_type' => WorkspaceChatParticipant::TYPE_HUMAN,
        'participant_key' => 'human:ops@relay.devoption.test',
        'display_name' => 'Relay Operator',
    ]);
    WorkspaceChatMessage::factory()->for($activeChat, 'chat')->create([
        'sender_type' => WorkspaceChatMessage::SENDER_HUMAN,
        'sender_key' => 'human:ops@relay.devoption.test',
        'sender_name' => 'Relay Operator',
        'body' => 'Remote conversations stay private to this workspace and do not enter the shared graph.',
    ]);

    $connection->forceFill([
        'active_workspace_id' => $workspace->getKey(),
    ])->save();
    $workspace->forceFill([
        'active_chat_id' => $activeChat->getKey(),
    ])->save();

    actingAs($user)
        ->withSession(['instance_connection.active_id' => $connection->getKey()]);

    get('/')
        ->assertSuccessful()
        ->assertSee('Relay Cloud')
        ->assertSee('Connections')
        ->assertSee('Relay Launch')
        ->assertSee('Ops Briefing')
        ->assertSee('# general')
        ->assertSee('Relay Operator')
        ->assertSee('ops@relay.devoption.test')
        ->assertSee('Remote conversations stay private to this workspace and do not enter the shared graph.')
        ->assertSee('Signed in as ops@relay.devoption.test.');
});
