<?php

use App\Livewire\Agents\Create as AgentCreate;
use App\Livewire\Agents\Edit as AgentEdit;
use App\Livewire\Agents\Index as AgentIndex;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Credentials\Create as CredentialCreate;
use App\Livewire\Credentials\Edit as CredentialEdit;
use App\Livewire\Credentials\Index as CredentialIndex;
use App\Livewire\Dashboard;
use App\Livewire\Tools\Create as ToolCreate;
use App\Livewire\Tools\Edit as ToolEdit;
use App\Livewire\Tools\Index as ToolIndex;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password', ResetPassword::class)->name('password.reset');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Agents
    Route::get('/agents', AgentIndex::class)->name('agents.index');
    Route::get('/agents/create', AgentCreate::class)->name('agents.create');
    Route::get('/agents/{agent}/edit', AgentEdit::class)->name('agents.edit');

    // Tools
    Route::get('/tools', ToolIndex::class)->name('tools.index');
    Route::get('/tools/create', ToolCreate::class)->name('tools.create');
    Route::get('/tools/{tool}/edit', ToolEdit::class)->name('tools.edit');

    // Admin only routes
    Route::middleware('admin')->group(function () {
        Route::get('/credentials', CredentialIndex::class)->name('credentials.index');
        Route::get('/credentials/create', CredentialCreate::class)->name('credentials.create');
        Route::get('/credentials/{credential}/edit', CredentialEdit::class)->name('credentials.edit');
    });

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
