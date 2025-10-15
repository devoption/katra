<?php

use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', Dashboard::class)->name('dashboard');

Route::get('/logout', function () {
    // Placeholder - will be replaced with Fortify
    return redirect('/');
})->name('logout');
