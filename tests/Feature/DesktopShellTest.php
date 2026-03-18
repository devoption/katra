<?php

test('the desktop shell exposes the katra bootstrap screen', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('NativePHP desktop shell')
        ->assertSee('composer native:dev')
        ->assertSee('Katra is taking shape as a graph-native workspace');
});
