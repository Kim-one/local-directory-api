<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware('web')->group(function () {
    Route::post('/login', Login::class);
    Route::post('/logout', Logout::class);
});
