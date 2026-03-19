<?php

test('the desktop shell exposes the katra bootstrap screen', function () {
    config()->set('surreal.autostart', false);
    config()->set('surreal.host', '127.0.0.1');
    config()->set('surreal.port', 18999);
    config()->set('surreal.endpoint', 'ws://127.0.0.1:18999');

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
        ->assertSee('Unavailable');
});
