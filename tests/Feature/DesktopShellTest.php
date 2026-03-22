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
        ->assertSee('Katra')
        ->assertSee('Workspaces')
        ->assertSee('Favorites')
        ->assertSee('Rooms')
        ->assertSee('Chats')
        ->assertSee('Create room')
        ->assertSee('Create chat')
        ->assertSee('Planner Agent')
        ->assertSee('Research Model')
        ->assertSee('# design-room')
        ->assertSee('Create workspace')
        ->assertSee('Workspace name')
        ->assertSee('Create room')
        ->assertSee('Room name')
        ->assertSee('Create chat')
        ->assertSee('Start conversation')
        ->assertSee('Contacts')
        ->assertSee('Search people, agents, and models')
        ->assertSee('Selected')
        ->assertSee('Available contacts')
        ->assertSee('No contacts selected yet.')
        ->assertSee('Server')
        ->assertSee('Katra Local')
        ->assertSee('Relay Cloud')
        ->assertSee('Research Model')
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
        ->assertSee('Manage connections')
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

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Katra')
        ->assertSee('# design-room')
        ->assertDontSee('Workspace navigation');
});

test('the desktop shell can switch the active mock workspace from the selector', function () {
    config()->set('pennant.default', 'array');
    config()->set('surreal.autostart', false);
    config()->set('surreal.host', '127.0.0.1');
    config()->set('surreal.port', 18999);
    config()->set('surreal.endpoint', 'ws://127.0.0.1:18999');
    config()->set('surreal.binary', 'surreal-missing-binary-for-desktop-shell-test');

    $this->get('/?workspace=design-lab')
        ->assertSuccessful()
        ->assertSee('Design Lab')
        ->assertSee('# shell-studies')
        ->assertSee('Shared room for people, models, and agents working inside Design Lab.')
        ->assertSee('Visual Agent')
        ->assertSee('Critique Agent');
});
