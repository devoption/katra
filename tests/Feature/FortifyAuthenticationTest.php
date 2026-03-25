<?php

use App\Models\InstanceConnection;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\FileStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

test('guests are redirected from the desktop shell to the login screen', function () {
    $this->get(route('home'))
        ->assertRedirect(route('login'));
});

test('the fortify auth screens render', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('Sign in to Katra')
        ->assertSee('Forgot password?')
        ->assertSee('This instance')
        ->assertSee('Server');

    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSee('Create your Katra account')
        ->assertSee('First name')
        ->assertSee('Last name');

    $this->get(route('password.request'))
        ->assertSuccessful()
        ->assertSee('Reset your password');

    $this->get(route('server.connect'))
        ->assertSuccessful()
        ->assertSee('Connect to a server')
        ->assertSee('Continue to server');
});

test('a guest can continue from the server connect screen into the in-app server credential step', function () {
    $this->post(route('server.connect.prepare'), [
        'server_url' => 'https://katra-server.test/',
    ])
        ->assertRedirect(route('server.connect'));

    $this->get(route('server.connect'))
        ->assertSuccessful()
        ->assertSee('katra-server.test')
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertSee('Connect to server');
});

test('a guest can sign into a remote katra server without leaving the client', function () {
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

    $this->post(route('server.connect.prepare'), [
        'server_url' => 'https://katra-server.test/',
    ])->assertRedirect(route('server.connect'));

    $this->post(route('server.connect.authenticate'), [
        'email' => 'ops@relay.devoption.test',
        'password' => 'password',
    ])->assertRedirect(route('home'));

    $user = User::query()->where('email', 'ops@relay.devoption.test')->first();
    $savedConnection = InstanceConnection::query()
        ->where('user_id', $user?->getKey())
        ->where('base_url', 'https://katra-server.test')
        ->first();

    $this->assertAuthenticated();

    expect($user)->not()->toBeNull()
        ->and($user?->first_name)->toBe('Ops')
        ->and($user?->last_name)->toBe('Bourgeois')
        ->and($savedConnection)->not()->toBeNull()
        ->and($savedConnection?->last_authenticated_at)->not()->toBeNull()
        ->and(data_get($savedConnection?->session_context, 'user.name'))->toBe('Ops Bourgeois')
        ->and(data_get($savedConnection?->session_context, 'cookies.katra_server_session'))->toBe('authenticated-session');

    Http::assertSent(function (HttpRequest $request): bool {
        if ($request->url() !== 'https://katra-server.test/login' || $request->method() !== 'POST') {
            return false;
        }

        return str_contains($request->body(), 'email=ops%40relay.devoption.test')
            && str_contains($request->body(), 'password=password')
            && $request->hasHeader('Cookie', 'katra_server_session=bootstrap-session');
    });
});

test('a guest remote sign-in updates an existing placeholder local user with the server profile', function () {
    $localUser = User::factory()->create([
        'first_name' => 'Derek',
        'last_name' => 'User',
        'name' => 'Derek User',
        'email' => 'derek@devoption.io',
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
            'first_name' => 'Derek',
            'last_name' => 'Bourgeois',
            'name' => 'Derek Bourgeois',
            'email' => 'derek@devoption.io',
        ], 200),
    ]);

    $this->post(route('server.connect.prepare'), [
        'server_url' => 'https://katra-server.test/',
    ])->assertRedirect(route('server.connect'));

    $this->post(route('server.connect.authenticate'), [
        'email' => 'derek@devoption.io',
        'password' => 'password',
    ])->assertRedirect(route('home'));

    expect($localUser->fresh())->not()->toBeNull()
        ->and($localUser->fresh()?->first_name)->toBe('Derek')
        ->and($localUser->fresh()?->last_name)->toBe('Bourgeois')
        ->and($localUser->fresh()?->name)->toBe('Derek Bourgeois');
});

test('the login rate limiter uses the file cache store', function () {
    config([
        'cache.default' => 'database',
        'cache.limiter' => 'file',
    ]);

    expect(cache()->driver(config('cache.limiter'))->getStore())->toBeInstanceOf(FileStore::class);
});

test('a user can register for a katra account', function () {
    $this->post(route('register'), [
        'first_name' => 'Derek',
        'last_name' => 'Bourgeois',
        'email' => 'derek@katra.io',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'derek@katra.io')->first();

    expect($user)->not->toBeNull()
        ->and($user?->first_name)->toBe('Derek')
        ->and($user?->last_name)->toBe('Bourgeois')
        ->and($user?->name)->toBe('Derek Bourgeois');
});

test('a user can sign in with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'derek@katra.io',
        'password' => 'password',
    ]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

test('a user cannot sign in with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'derek@katra.io',
        'password' => 'password',
    ]);

    $this->from(route('login'))
        ->post(route('login'), [
            'email' => $user->email,
            'password' => 'not-the-right-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('an authenticated user can log out from katra', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect('/');

    $this->assertGuest();
});

test('a user can request a password reset link', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'derek@katra.io',
    ]);

    $this->post(route('password.email'), [
        'email' => $user->email,
    ])->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('a user can reset their password with a valid token', function () {
    $user = User::factory()->create([
        'email' => 'derek@katra.io',
        'password' => 'password',
    ]);

    $token = Password::broker()->createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertSessionHasNoErrors();

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});
