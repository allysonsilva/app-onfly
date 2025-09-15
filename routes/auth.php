<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;
use App\Http\Controllers\Auth\Register;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->name('guest.')->group(function () {
    Route::post('login', Login::class)->name('login');
    Route::post('register', Register::class)->name('register');
});

Route::middleware('auth')->group(function () {
    Route::delete('logout', Logout::class)->name('logout');
});
