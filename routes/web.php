<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::view('/connect-server', 'auth.connect-server')->name('server.connect');

Route::middleware('auth')->get('/', HomeController::class)->name('home');
