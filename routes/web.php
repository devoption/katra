<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstanceConnectionController;
use App\Livewire\FoundationPreview;
use Illuminate\Support\Facades\Route;

Route::get('/foundation-preview', FoundationPreview::class)->name('foundation.preview');

Route::get('/connect-server', [InstanceConnectionController::class, 'showServerConnect'])->name('server.connect');
Route::post('/connect-server', [InstanceConnectionController::class, 'prepareServerLogin'])->name('server.connect.prepare');
Route::post('/connect-server/authenticate', [InstanceConnectionController::class, 'authenticateGuestServer'])->name('server.connect.authenticate');

Route::middleware('auth')->group(function (): void {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/_katra/profile', [InstanceConnectionController::class, 'profile'])->name('profile.current');
    Route::post('/connections', [InstanceConnectionController::class, 'store'])->name('connections.store');
    Route::patch('/connections/{instanceConnection}', [InstanceConnectionController::class, 'update'])->name('connections.update');
    Route::delete('/connections/{instanceConnection}', [InstanceConnectionController::class, 'destroy'])->name('connections.destroy');
    Route::post('/connections/{instanceConnection}/activate', [InstanceConnectionController::class, 'activate'])->name('connections.activate');
    Route::get('/connections/{instanceConnection}/connect', [InstanceConnectionController::class, 'connect'])->name('connections.connect');
    Route::post('/connections/{instanceConnection}/connect', [InstanceConnectionController::class, 'authenticate'])->name('connections.authenticate');
});
