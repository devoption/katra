<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
        ->assertSee('Forgot password?');

    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSee('Create your Katra account');

    $this->get(route('password.request'))
        ->assertSuccessful()
        ->assertSee('Reset your password');
});

test('a user can register for a local katra account', function () {
    $this->post(route('register'), [
        'name' => 'Derek Bourgeois',
        'email' => 'derek@katra.io',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticated();

    expect(User::query()->where('email', 'derek@katra.io')->exists())->toBeTrue();
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
