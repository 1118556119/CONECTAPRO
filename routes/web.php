<?php

use Illuminate\Support\Facades\Route;

// API routes
Route::prefix('api')->group(function () {
    // Las rutas API se definen en routes/api.php
});

// Todas las demás rutas se dirigen a la SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');