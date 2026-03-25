<?php

use App\Features\Desktop\AgentPresence;
use App\Features\Desktop\ArtifactSurfaces;
use App\Features\Desktop\ConversationChannels;
use App\Features\Desktop\MvpShell;
use App\Features\Desktop\TaskSurfaces;
use App\Features\Desktop\WorkspaceNavigation;
use App\Models\User;
use App\Support\Features\DesktopUi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Attributes\Name;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

test('desktop ui rollout uses the desktop pennant scope and feature naming convention', function () {
    config()->set('pennant.default', 'array');

    expect(DesktopUi::scope())->toBe('desktop-ui')
        ->and(DesktopUi::features())->toBe([
            MvpShell::class,
            WorkspaceNavigation::class,
            ConversationChannels::class,
            TaskSurfaces::class,
            ArtifactSurfaces::class,
            AgentPresence::class,
        ]);

    foreach (DesktopUi::features() as $feature) {
        $attributes = (new ReflectionClass($feature))->getAttributes(Name::class);

        expect($attributes)->toHaveCount(1)
            ->and($attributes[0]->newInstance()->name)->toStartWith('ui.desktop.');
    }
});

test('desktop ui rollout defaults are resolved through pennant', function () {
    config()->set('pennant.default', 'array');

    expect(DesktopUi::states())->toMatchArray([
        'ui.desktop.mvp-shell' => true,
        'ui.desktop.workspace-navigation' => false,
        'ui.desktop.conversation-channels' => false,
        'ui.desktop.task-surfaces' => false,
        'ui.desktop.artifact-surfaces' => false,
        'ui.desktop.agent-presence' => false,
    ]);
});

test('desktop ui rollout falls back to defaults when the Pennant database store is not ready', function () {
    config()->set('pennant.default', 'database');
    config()->set('pennant.stores.database.connection', 'pennant_fallback');
    config()->set('database.connections.pennant_fallback', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    expect(DesktopUi::states())->toMatchArray([
        'ui.desktop.mvp-shell' => true,
        'ui.desktop.workspace-navigation' => false,
        'ui.desktop.conversation-channels' => false,
        'ui.desktop.task-surfaces' => false,
        'ui.desktop.artifact-surfaces' => false,
        'ui.desktop.agent-presence' => false,
    ]);
});

test('desktop ui surfaces can be staged on without changing the shell implementation', function () {
    config()->set('pennant.default', 'array');

    Feature::for(DesktopUi::scope())->activate(WorkspaceNavigation::class);

    $this->actingAs(User::factory()->create([
        'id' => 1,
        'first_name' => 'Derek',
        'last_name' => 'Bourgeois',
        'name' => 'Derek Bourgeois',
        'email' => 'derek@katra.io',
    ]));

    expect(DesktopUi::active(WorkspaceNavigation::class))->toBeTrue()
        ->and(DesktopUi::states()['ui.desktop.workspace-navigation'])->toBeTrue();

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('# design-room')
        ->assertSee('Katra');
});

test('the desktop shell can be hidden behind the mvp flag', function () {
    config()->set('pennant.default', 'array');

    Feature::for(DesktopUi::scope())->deactivate(MvpShell::class);

    $this->actingAs(User::factory()->create([
        'id' => 1,
        'first_name' => 'Derek',
        'last_name' => 'Bourgeois',
        'name' => 'Derek Bourgeois',
        'email' => 'derek@katra.io',
    ]));

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('The MVP workspace shell is currently hidden.')
        ->assertDontSee('# design-room')
        ->assertDontSee('Notes');
});
