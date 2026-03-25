<?php

use App\Models\InstanceConnection;
use App\Models\User;
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
        ->assertSee('Favorites')
        ->assertSee('Rooms')
        ->assertSee('Chats')
        ->assertSee('Create room')
        ->assertSee('Create chat')
        ->assertSee('Planner Agent')
        ->assertSee('Research Model')
        ->assertSee('# design-room')
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
        ->assertSee('Open context panel')
        ->assertSee('Close context panel')
        ->assertSee('Pin context panel')
        ->assertSee('Resize context panel')
        ->assertSee('Manage people')
        ->assertSee('Nodes')
        ->assertSee('Open')
        ->assertSee('Closed')
        ->assertSee('In review')
        ->assertSee('Assign to agent')
        ->assertSee('Assign')
        ->assertSee('Choose an agent')
        ->assertSee('Context Agent')
        ->assertSee('Attach file')
        ->assertSee('Toggle voice mode')
        ->assertSee('Send message')
        ->assertSee('Message # design-room')
        ->assertSee('Voice mode selected')
        ->assertSee('Tighten the room layout, spacing, and navigation so the shell feels like an app instead of a staged page.')
        ->assertSee('Derek Bourgeois')
        ->assertSee('derek@katra.io')
        ->assertSee('Profile settings')
        ->assertSee('Workspace settings')
        ->assertSee('Administration')
        ->assertSee('Light')
        ->assertSee('Dark')
        ->assertSee('System')
        ->assertSee('Log out')
        ->assertDontSee('Create workspace')
        ->assertDontSee('Workspace name')
        ->assertDontSee('Workspaces')
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
        ->assertSee('# design-room')
        ->assertDontSee('Workspace navigation');
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

    actingAs($user)
        ->withSession(['instance_connection.active_id' => $connection->getKey()]);

    get('/')
        ->assertSuccessful()
        ->assertSee('Relay Cloud')
        ->assertSee('Connections')
        ->assertSee('# relay-ops')
        ->assertSee('Ops Agent')
        ->assertSee('Routing Agent')
        ->assertSee('Relay Operator')
        ->assertSee('ops@relay.devoption.test')
        ->assertSee('Signed in as ops@relay.devoption.test.');
});
