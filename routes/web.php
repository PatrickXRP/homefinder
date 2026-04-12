<?php

use App\Http\Controllers\Auth\VoidAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/auth/redirect', [VoidAuthController::class, 'redirect'])->name('voidauth.redirect');
Route::get('/auth/callback', [VoidAuthController::class, 'callback'])->name('voidauth.callback');
Route::post('/auth/logout', [VoidAuthController::class, 'logout'])->name('voidauth.logout');
