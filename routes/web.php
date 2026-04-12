<?php

use App\Http\Controllers\Auth\VoidAuthController;
use App\Http\Controllers\KidsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// Kids app (standalone, no VoidAuth needed)
Route::get('/kids', [KidsController::class, 'login']);
Route::post('/kids/login', [KidsController::class, 'authenticate']);
Route::get('/kids/logout', [KidsController::class, 'logout']);
Route::get('/kids/swipe', [KidsController::class, 'swipe']);
Route::post('/kids/swipe', [KidsController::class, 'doSwipe']);
Route::get('/kids/huizen', [KidsController::class, 'huizen']);
Route::get('/kids/woning/{id}', [KidsController::class, 'woning']);

Route::get('/auth/redirect', [VoidAuthController::class, 'redirect'])->name('voidauth.redirect');
Route::get('/auth/callback', [VoidAuthController::class, 'callback'])->name('voidauth.callback');
Route::post('/auth/logout', [VoidAuthController::class, 'logout'])->name('voidauth.logout');
