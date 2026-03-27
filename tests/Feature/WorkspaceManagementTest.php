<?php

use App\Models\InstanceConnection;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Connections\InstanceConnectionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

test('active workspace resolution creates a default workspace for the active connection', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'name' => 'Katra',
        'base_url' => 'https://katra.test',
        'active_workspace_id' => null,
    ]);

    $workspace = app(InstanceConnectionManager::class)->activeWorkspaceFor($connection);

    expect($workspace->name)->toBe('General')
        ->and($workspace->slug)->toBe('general')
        ->and($connection->fresh()->active_workspace_id)->toBe($workspace->getKey())
        ->and($connection->workspaces()->count())->toBe(1);
});

test('an authenticated user can create a workspace for the active connection', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'name' => 'Katra',
        'base_url' => 'https://katra.test',
    ]);

    actingAs($user)->withSession([
        'instance_connection.active_id' => $connection->getKey(),
    ]);

    post(route('workspaces.store'), [
        'workspace_name' => 'Project Atlas',
    ])->assertRedirect(route('home'));

    $workspace = $connection->workspaces()->where('slug', 'project-atlas')->first();

    expect($workspace)->not()->toBeNull()
        ->and($workspace?->name)->toBe('Project Atlas')
        ->and($connection->fresh()->active_workspace_id)->toBe($workspace?->getKey());
});

test('an authenticated user can switch the active workspace for the active connection', function () {
    $user = User::factory()->create();
    $connection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'name' => 'Katra',
        'base_url' => 'https://katra.test',
    ]);
    $generalWorkspace = Workspace::factory()->for($connection)->create([
        'name' => 'General',
        'slug' => 'general',
    ]);
    $projectWorkspace = Workspace::factory()->for($connection)->create([
        'name' => 'Project Atlas',
        'slug' => 'project-atlas',
    ]);

    $connection->forceFill([
        'active_workspace_id' => $generalWorkspace->getKey(),
    ])->save();

    actingAs($user)->withSession([
        'instance_connection.active_id' => $connection->getKey(),
    ]);

    post(route('workspaces.activate', $projectWorkspace))
        ->assertRedirect(route('home'));

    expect($connection->fresh()->active_workspace_id)->toBe($projectWorkspace->getKey());
});

test('each connection keeps its own active workspace', function () {
    config()->set('pennant.default', 'array');
    config()->set('surreal.autostart', false);
    config()->set('surreal.host', '127.0.0.1');
    config()->set('surreal.port', 18999);
    config()->set('surreal.endpoint', 'ws://127.0.0.1:18999');
    config()->set('surreal.binary', 'surreal-missing-binary-for-workspace-test');

    $user = User::factory()->create([
        'first_name' => 'Derek',
        'last_name' => 'Bourgeois',
        'name' => 'Derek Bourgeois',
        'email' => 'derek@katra.io',
    ]);

    $localConnection = InstanceConnection::factory()->for($user)->currentInstance()->create([
        'name' => 'Katra',
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
        'session_context' => [
            'user' => [
                'name' => 'Ops Bourgeois',
                'email' => 'ops@relay.devoption.test',
            ],
        ],
    ]);

    $localWorkspace = Workspace::factory()->for($localConnection)->create([
        'name' => 'Product Atlas',
        'slug' => 'product-atlas',
    ]);
    $serverWorkspace = Workspace::factory()->for($serverConnection)->create([
        'name' => 'Relay Launch',
        'slug' => 'relay-launch',
    ]);

    $localConnection->forceFill([
        'active_workspace_id' => $localWorkspace->getKey(),
    ])->save();
    $serverConnection->forceFill([
        'active_workspace_id' => $serverWorkspace->getKey(),
    ])->save();

    actingAs($user)->withSession([
        'instance_connection.active_id' => $localConnection->getKey(),
    ]);

    get(route('home'))
        ->assertSuccessful()
        ->assertSee('Product Atlas')
        ->assertDontSee('Relay Launch');

    post(route('connections.activate', $serverConnection))
        ->assertRedirect(route('home'));

    get(route('home'))
        ->assertSuccessful()
        ->assertSee('Relay Launch');

    post(route('connections.activate', $localConnection))
        ->assertRedirect(route('home'));

    get(route('home'))
        ->assertSuccessful()
        ->assertSee('Product Atlas')
        ->assertDontSee('Relay Launch');
});

test('an authenticated user cannot activate another users workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();

    actingAs($user);

    post(route('workspaces.activate', $workspace))
        ->assertNotFound();
});
