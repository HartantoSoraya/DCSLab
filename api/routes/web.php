<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    $dashboardUrl = env('APP_URL').'/dashboard/main'.'?'.http_build_query(request()->query());

    return redirect()->away($dashboardUrl);
})->name('home');

Route::get('/do-reset-password', function () {
    $resetUrl = env('APP_URL').'/auth/reset-password'.'?'.http_build_query(request()->query());

    return redirect()->away($resetUrl);
})->name('password.reset');
