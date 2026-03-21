<?php

test('the desktop shell exposes the katra bootstrap screen', function () {
    config()->set('pennant.default', 'array');
    config()->set('surreal.autostart', false);
    config()->set('surreal.host', '127.0.0.1');
    config()->set('surreal.port', 18999);
    config()->set('surreal.endpoint', 'ws://127.0.0.1:18999');
    config()->set('surreal.binary', 'surreal-missing-binary-for-desktop-shell-test');

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('NativePHP desktop shell')
        ->assertSee('composer native:dev')
        ->assertSee('Katra is taking shape as a graph-native workspace')
        ->assertSee('Surreal Foundation')
        ->assertSee('Bundled preview')
        ->assertSee('local Surreal runtime')
        ->assertSee('Runtime')
        ->assertSee('Binary')
        ->assertSee('Endpoint')
        ->assertSee('Unavailable')
        ->assertDontSee('Workspace navigation pilot');
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

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('NativePHP desktop shell')
        ->assertDontSee('Workspace navigation pilot');
});
