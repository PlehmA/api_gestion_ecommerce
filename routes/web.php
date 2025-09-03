<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Ruta temporal para evitar el error "Route [login] not defined"
Route::get('/login', function () {
    return response()->json(['message' => 'Esta es una API. Use POST /api/login para autenticarse.'], 404);
})->name('login');
