<?php

use App\Models\InstanceConnection;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceChat;
use App\Models\WorkspaceChatMessage;
use App\Models\WorkspaceChatParticipant;
use App\Support\Connections\InstanceConnectionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

test('an authenticated user can save a server connection profile', function () {
    $user = User::factory()->create();

    actingAs($user);

    post(route('connections.store'), [
        'name' => 'Relay Cloud',
        'base_url' => 'https://relay.devoption.test/',
    ])
        ->assertRedirect()
        ->assertSessionHas('instance_connection.active_id');

    $connection = InstanceConnection::query()
        ->where('user_id', $user->getKey())
        ->where('kind', InstanceConnection::KIND_SERVER)
        ->first();

    expect($connection)->not()->toBeNull()
        ->and($connection?->name)->toBe('Relay Cloud')
        ->and($connection?->base_url)->toBe('https://relay.devoption.test')
        ->and($connection?->last_authenticated_at)->toBeNull()
        ->and($connection?->last_used_at)->not()->toBeNull();
});

test('an authenticated user can save a server connection profile with only the server url', function () {
    $user = User::factory()->create();

    actingAs($user);

    post(route('connections.store'), [
        'base_url' => 'https://katra-server.test/',
    ])
        ->assertRedirect();

    $connection = InstanceConnection::query()
        ->where('user_id', $user->getKey())
        ->where('kind', InstanceConnection::KIND_SERVER)
        ->first();

    expect($connection)->not()->toBeNull()
        ->and($connection?->name)->toBe('Katra Server')
        ->and($connection?->base_url)->toBe('https://katra-server.test');
});

test('an authenticated user can activate one of their saved connections', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->create([
        'name' => 'Relay Cloud',
        'kind' => InstanceConnection::KIND_SERVER,
        'base_url' => 'https://relay.devoption.test',
    ]);

    actingAs($user);

    post(route('connections.activate', $connection))
        ->assertRedirect(route('connections.connect', $connection))
        ->assertSessionHas('instance_connection.active_id', $connection->getKey());

    $connection->refresh();

    expect($connection->last_authenticated_at)->toBeNull()
        ->and($connection->last_used_at)->not()->toBeNull();
});

test('active connection resolution creates a current instance connection when no connection is active', function () {
    $user = User::factory()->create();

    InstanceConnection::factory()->for($user)->create([
        'name' => 'Relay Cloud',
        'kind' => InstanceConnection::KIND_SERVER,
        'base_url' => 'https://relay.devoption.test',
        'last_authenticated_at' => now(),
        'last_used_at' => now()->addMinute(),
    ]);

    $manager = app(InstanceConnectionManager::class);
    $activeConnection = $manager->activeConnectionFor($user, 'https://katra.test', app('session.store'));
    $connections = $manager->connectionsFor($user);

    expect($activeConnection->kind)->toBe(InstanceConnection::KIND_CURRENT_INSTANCE)
        ->and($connections->firstWhere('kind', InstanceConnection::KIND_CURRENT_INSTANCE))->not()->toBeNull()
        ->and($connections->firstWhere('kind', InstanceConnection::KIND_CURRENT_INSTANCE)?->base_url)->toBe('https://katra.test');
});

test('the current instance connection uses the configured app name by default', function () {
    config()->set('app.name', 'Laravel');

    $user = User::factory()->create();

    $connection = app(InstanceConnectionManager::class)->ensureCurrentInstanceConnection($user, 'https://katra.test');

    expect($connection->name)->toBe('Laravel');
});

test('an authenticated user can rename their current instance connection', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'name' => 'Katra',
    ]);

    actingAs($user);

    patch(route('connections.update', $connection), [
        'name' => 'Studio',
    ])->assertRedirect(route('home'));

    expect($connection->fresh()->name)->toBe('Studio');
});

test('a blank current instance connection name resets to the app name', function () {
    config()->set('app.name', 'Katra');

    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'name' => 'Studio',
    ]);

    actingAs($user);

    patch(route('connections.update', $connection), [
        'name' => '',
    ])->assertRedirect(route('home'));

    expect($connection->fresh()->name)->toBe('Katra');
});

test('an authenticated user can update a saved server connection profile', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->create([
        'name' => 'Old Relay',
        'kind' => InstanceConnection::KIND_SERVER,
        'base_url' => 'https://relay.devoption.test',
        'last_authenticated_at' => now(),
        'session_context' => ['email' => 'ops@relay.devoption.test'],
    ]);

    actingAs($user)->withSession([
        'instance_connection.active_id' => $connection->getKey(),
    ]);

    patch(route('connections.update', $connection), [
        'name' => 'Katra Server',
        'base_url' => 'https://katra-server.test',
    ])->assertRedirect(route('connections.connect', $connection));

    $connection->refresh();

    expect($connection->name)->toBe('Katra Server')
        ->and($connection->base_url)->toBe('https://katra-server.test')
        ->and($connection->last_authenticated_at)->toBeNull()
        ->and($connection->session_context)->toBeNull();
});

test('an authenticated user can delete an inactive saved server connection', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->create([
        'kind' => InstanceConnection::KIND_SERVER,
    ]);

    actingAs($user);

    delete(route('connections.destroy', $connection))
        ->assertRedirect(route('home'));

    expect(InstanceConnection::query()->find($connection->getKey()))->toBeNull();
});

test('deleting the active server connection falls back to the current instance connection', function () {
    $user = User::factory()->create();
    $currentInstanceConnection = InstanceConnection::factory()->for($user)->create([
        'name' => 'Katra',
        'kind' => InstanceConnection::KIND_CURRENT_INSTANCE,
        'base_url' => 'https://katra.test',
        'last_authenticated_at' => now(),
        'last_used_at' => now()->subMinute(),
    ]);
    $serverConnection = InstanceConnection::factory()->for($user)->create([
        'name' => 'Katra Server',
        'kind' => InstanceConnection::KIND_SERVER,
        'base_url' => 'https://katra-server.test',
        'last_authenticated_at' => now(),
        'last_used_at' => now(),
    ]);

    actingAs($user)->withSession([
        'instance_connection.active_id' => $serverConnection->getKey(),
    ]);

    delete(route('connections.destroy', $serverConnection))
        ->assertRedirect(route('home'))
        ->assertSessionHas('instance_connection.active_id', $currentInstanceConnection->getKey());

    expect(InstanceConnection::query()->find($serverConnection->getKey()))->toBeNull();
});

test('an authenticated user cannot activate another users connection', function () {
    $user = User::factory()->create();
    $otherConnection = InstanceConnection::factory()->create();

    actingAs($user);

    post(route('connections.activate', $otherConnection))
        ->assertNotFound();
});

test('the shell uses the currently active connection profile for the session', function () {
    config()->set('pennant.default', 'array');
    config()->set('surreal.autostart', false);
    config()->set('surreal.host', '127.0.0.1');
    config()->set('surreal.port', 18999);
    config()->set('surreal.endpoint', 'ws://127.0.0.1:18999');
    config()->set('surreal.binary', 'surreal-missing-binary-for-connection-test');

    $user = User::factory()->create([
        'first_name' => 'Derek',
        'last_name' => 'Bourgeois',
        'name' => 'Derek Bourgeois',
        'email' => 'derek@katra.io',
    ]);

    $connection = InstanceConnection::factory()->for($user)->create([
        'name' => 'Relay Cloud',
        'kind' => InstanceConnection::KIND_SERVER,
        'base_url' => 'https://relay.devoption.test',
        'last_authenticated_at' => now(),
        'last_used_at' => now(),
        'session_context' => [
            'user' => [
                'name' => 'Relay Ops',
                'email' => 'ops@relay.devoption.test',
            ],
        ],
    ]);
    $workspace = Workspace::factory()->for($connection)->create([
        'name' => 'Relay Launch',
        'slug' => 'relay-launch',
    ]);
    $chat = WorkspaceChat::factory()->for($workspace, 'workspace')->create([
        'name' => 'Ops Briefing',
        'slug' => 'ops-briefing',
        'kind' => WorkspaceChat::KIND_GROUP,
        'visibility' => WorkspaceChat::VISIBILITY_PRIVATE,
    ]);
    WorkspaceChatParticipant::factory()->for($chat, 'chat')->create([
        'user_id' => null,
        'participant_type' => WorkspaceChatParticipant::TYPE_AGENT,
        'participant_key' => 'agent:ops-agent',
        'display_name' => 'Ops Agent',
    ]);
    WorkspaceChatMessage::factory()->for($chat, 'chat')->create([
        'sender_type' => WorkspaceChatMessage::SENDER_AGENT,
        'sender_key' => 'agent:ops-agent',
        'sender_name' => 'Ops Agent',
        'body' => 'Relay Cloud keeps private chats scoped to the active workspace.',
    ]);

    $connection->forceFill([
        'active_workspace_id' => $workspace->getKey(),
    ])->save();
    $workspace->forceFill([
        'active_chat_id' => $chat->getKey(),
    ])->save();

    actingAs($user)->withSession([
        'instance_connection.active_id' => $connection->getKey(),
    ]);

    get(route('home'))
        ->assertSuccessful()
        ->assertSee('Relay Cloud')
        ->assertSee('Relay Launch')
        ->assertSee('# general')
        ->assertSee('Ops Briefing')
        ->assertSee('Ops Agent')
        ->assertSee('Relay Cloud keeps private chats scoped to the active workspace.')
        ->assertSee('Relay Ops')
        ->assertSee('ops@relay.devoption.test');
});

test('the connection list only includes the authenticated users connections', function () {
    config()->set('pennant.default', 'array');
    config()->set('surreal.autostart', false);
    config()->set('surreal.host', '127.0.0.1');
    config()->set('surreal.port', 18999);
    config()->set('surreal.endpoint', 'ws://127.0.0.1:18999');
    config()->set('surreal.binary', 'surreal-missing-binary-for-connection-test');

    $localUser = User::factory()->create([
        'first_name' => 'Derek',
        'last_name' => 'Katra',
        'name' => 'Derek Katra',
        'email' => 'derek@katra.io',
    ]);

    $remoteUser = User::factory()->create([
        'first_name' => 'Derek',
        'last_name' => 'Bourgeois',
        'name' => 'Derek Bourgeois',
        'email' => 'derek@devoption.io',
    ]);

    InstanceConnection::factory()->for($localUser)->create([
        'name' => 'Katra',
        'kind' => InstanceConnection::KIND_CURRENT_INSTANCE,
        'base_url' => 'https://katra.test',
        'last_authenticated_at' => now(),
        'last_used_at' => now()->subMinute(),
    ]);

    InstanceConnection::factory()->for($remoteUser)->create([
        'name' => 'Katra Server',
        'kind' => InstanceConnection::KIND_SERVER,
        'base_url' => 'https://katra-server.test',
        'last_authenticated_at' => now(),
        'last_used_at' => now(),
        'session_context' => [
            'email' => 'derek@devoption.io',
            'user' => [
                'name' => 'Derek Bourgeois',
                'email' => 'derek@devoption.io',
                'first_name' => 'Derek',
                'last_name' => 'Bourgeois',
            ],
        ],
    ]);

    actingAs($localUser);

    get(route('home'))
        ->assertSuccessful()
        ->assertSee('Derek Katra')
        ->assertSee('derek@katra.io')
        ->assertDontSee('Katra Server');
});

test('guest server sign-in does not create a current-instance connection automatically', function () {
    Http::fake([
        'https://katra-server.test/login' => Http::sequence()
            ->push(
                '<form method="POST"><input type="hidden" name="_token" value="csrf-token-123" /></form>',
                200,
                ['Set-Cookie' => ['katra_server_session=bootstrap-session; path=/; httponly']],
            )
            ->push(
                '',
                302,
                [
                    'Location' => 'https://katra-server.test/',
                    'Set-Cookie' => ['katra_server_session=authenticated-session; path=/; httponly'],
                ],
            ),
        'https://katra-server.test/' => Http::response('<html>Relay Cloud</html>', 200),
        'https://katra-server.test/_katra/profile' => Http::response([
            'first_name' => 'Ops',
            'last_name' => 'Bourgeois',
            'name' => 'Ops Bourgeois',
            'email' => 'ops@relay.devoption.test',
        ], 200),
    ]);

    post(route('server.connect.prepare'), [
        'server_url' => 'https://katra-server.test/',
    ])->assertRedirect(route('server.connect'));

    post(route('server.connect.authenticate'), [
        'email' => 'ops@relay.devoption.test',
        'password' => 'password',
    ])->assertRedirect(route('home'));

    $user = User::query()->where('email', 'ops@relay.devoption.test')->firstOrFail();

    expect($user->instanceConnections()->where('kind', InstanceConnection::KIND_CURRENT_INSTANCE)->exists())->toBeFalse()
        ->and($user->instanceConnections()->where('kind', InstanceConnection::KIND_SERVER)->count())->toBe(1);
});

test('the shell falls back to legacy remote connection email metadata for the profile surface', function () {
    config()->set('pennant.default', 'array');
    config()->set('surreal.autostart', false);
    config()->set('surreal.host', '127.0.0.1');
    config()->set('surreal.port', 18999);
    config()->set('surreal.endpoint', 'ws://127.0.0.1:18999');
    config()->set('surreal.binary', 'surreal-missing-binary-for-connection-test');

    $user = User::factory()->create([
        'first_name' => 'Derek',
        'last_name' => 'Bourgeois',
        'name' => 'Derek Bourgeois',
        'email' => 'derek@katra.io',
    ]);

    $connection = InstanceConnection::factory()->for($user)->create([
        'name' => 'Katra Server',
        'kind' => InstanceConnection::KIND_SERVER,
        'base_url' => 'https://katra-server.test',
        'last_authenticated_at' => now(),
        'last_used_at' => now(),
        'session_context' => [
            'email' => 'derek@devoption.io',
        ],
    ]);

    actingAs($user)->withSession([
        'instance_connection.active_id' => $connection->getKey(),
    ]);

    get(route('home'))
        ->assertSuccessful()
        ->assertSee('Derek User')
        ->assertSee('derek@devoption.io');
});

test('the shell redirects to the server connection screen when the active server is not authenticated', function () {
    config()->set('pennant.default', 'array');
    config()->set('surreal.autostart', false);
    config()->set('surreal.host', '127.0.0.1');
    config()->set('surreal.port', 18999);
    config()->set('surreal.endpoint', 'ws://127.0.0.1:18999');
    config()->set('surreal.binary', 'surreal-missing-binary-for-connection-test');

    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->create([
        'name' => 'Relay Cloud',
        'kind' => InstanceConnection::KIND_SERVER,
        'base_url' => 'https://katra-server.test',
        'last_authenticated_at' => null,
    ]);

    actingAs($user)->withSession([
        'instance_connection.active_id' => $connection->getKey(),
    ]);

    get(route('home'))
        ->assertRedirect(route('connections.connect', $connection));
});

test('an authenticated user can sign into a saved server connection', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->create([
        'name' => 'Relay Cloud',
        'kind' => InstanceConnection::KIND_SERVER,
        'base_url' => 'https://katra-server.test',
        'last_authenticated_at' => null,
    ]);

    Http::fake([
        'https://katra-server.test/login' => Http::sequence()
            ->push(
                '<form method="POST"><input type="hidden" name="_token" value="csrf-token-123" /></form>',
                200,
                ['Set-Cookie' => ['katra_server_session=bootstrap-session; path=/; httponly']],
            )
            ->push(
                '',
                302,
                [
                    'Location' => 'https://katra-server.test/',
                    'Set-Cookie' => ['katra_server_session=authenticated-session; path=/; httponly'],
                ],
            ),
        'https://katra-server.test/' => Http::response('<html>Relay Cloud</html>', 200),
        'https://katra-server.test/_katra/profile' => Http::response([
            'first_name' => 'Ops',
            'last_name' => 'Bourgeois',
            'name' => 'Ops Bourgeois',
            'email' => 'ops@relay.devoption.test',
        ], 200),
    ]);

    actingAs($user);

    post(route('connections.authenticate', $connection), [
        'email' => 'ops@relay.devoption.test',
        'password' => 'password',
    ])->assertRedirect(route('home'));

    $connection->refresh();

    expect($connection->last_authenticated_at)->not()->toBeNull()
        ->and(data_get($connection->session_context, 'email'))->toBe('ops@relay.devoption.test')
        ->and(data_get($connection->session_context, 'user.name'))->toBe('Ops Bourgeois')
        ->and(data_get($connection->session_context, 'user.last_name'))->toBe('Bourgeois')
        ->and(data_get($connection->session_context, 'user.email'))->toBe('ops@relay.devoption.test')
        ->and(data_get($connection->session_context, 'cookies.katra_server_session'))->toBe('authenticated-session');

    Http::assertSent(function (HttpRequest $request): bool {
        if ($request->url() !== 'https://katra-server.test/login' || $request->method() !== 'POST') {
            return false;
        }

        return str_contains($request->body(), '_token=csrf-token-123')
            && str_contains($request->body(), 'email=ops%40relay.devoption.test')
            && str_contains($request->body(), 'password=password')
            && $request->hasHeader('Cookie', 'katra_server_session=bootstrap-session');
    });

    Http::assertSent(function (HttpRequest $request): bool {
        return $request->url() === 'https://katra-server.test/'
            && $request->method() === 'GET'
            && $request->hasHeader('Cookie', 'katra_server_session=authenticated-session');
    });
});
