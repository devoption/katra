<?php

use App\Livewire\Agents\Create as AgentCreate;
use App\Livewire\Agents\Edit as AgentEdit;
use App\Livewire\Agents\Index as AgentIndex;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Contexts\Create as ContextCreate;
use App\Livewire\Contexts\Edit as ContextEdit;
use App\Livewire\Contexts\Index as ContextIndex;
use App\Livewire\Credentials\Create as CredentialCreate;
use App\Livewire\Credentials\Edit as CredentialEdit;
use App\Livewire\Credentials\Index as CredentialIndex;
use App\Livewire\Dashboard;
use App\Livewire\Tools\Create as ToolCreate;
use App\Livewire\Tools\Edit as ToolEdit;
use App\Livewire\Tools\Index as ToolIndex;
use App\Livewire\Workflows\Create as WorkflowCreate;
use App\Livewire\Workflows\Edit as WorkflowEdit;
use App\Livewire\Workflows\Index as WorkflowIndex;
use App\Livewire\Workflows\Show as WorkflowShow;
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

    // Contexts
    Route::get('/contexts', ContextIndex::class)->name('contexts.index');
    Route::get('/contexts/create', ContextCreate::class)->name('contexts.create');
    Route::get('/contexts/{context}/edit', ContextEdit::class)->name('contexts.edit');

    // Workflows
    Route::get('/workflows', WorkflowIndex::class)->name('workflows.index');
    Route::get('/workflows/create', WorkflowCreate::class)->name('workflows.create');
    Route::get('/workflows/{workflow}', WorkflowShow::class)->name('workflows.show');
    Route::get('/workflows/{workflow}/edit', WorkflowEdit::class)->name('workflows.edit');

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
