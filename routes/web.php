<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', function () {
        return view('katra::pages.home');
    })->name('katra.home');

    Route::get('/users', function () {
        return view('katra::pages.access.users.index');
    })->name('katra.users.index');

    Route::get('/settings', function () {
        return view('katra::pages.settings.index');
    })->name('katra.settings');

    Route::get('/profile', function () {
        return view('katra::pages.profile.index');
    })->name('katra.profile');
});
