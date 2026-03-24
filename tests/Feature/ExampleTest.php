<?php

test('guests are redirected to the login screen', function () {
    $this->get('/')
        ->assertRedirect(route('login'));
});
