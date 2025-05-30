<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TechnicianProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);

// Rutas para OAuth
Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

// Rutas protegidas que requieren autenticación
Route::middleware('auth:sanctum')->group(function () {
    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);
    
    // Perfil del usuario
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::post('/', [ProfileController::class, 'update']); // Alternativa para subir archivos
    });

    // Perfiles de técnicos (para administración)
    Route::prefix('technicians')->group(function () {
        Route::get('/', [TechnicianProfileController::class, 'index']);
        Route::get('/{id}', [TechnicianProfileController::class, 'show']);
        Route::post('/', [TechnicianProfileController::class, 'store']);
        Route::put('/{id}', [TechnicianProfileController::class, 'update']);
        Route::delete('/{id}', [TechnicianProfileController::class, 'destroy']);
    });

    // Solicitudes de servicio
    Route::prefix('service-requests')->group(function () {
        Route::get('/', [ServiceRequestController::class, 'index']);
        Route::post('/', [ServiceRequestController::class, 'store']);
        Route::get('/{id}', [ServiceRequestController::class, 'show']);
        Route::put('/{id}', [ServiceRequestController::class, 'update']);
        Route::delete('/{id}', [ServiceRequestController::class, 'destroy']);
    });

    // Reseñas
    Route::prefix('reviews')->group(function () {
        Route::post('/', [ReviewController::class, 'store']);
        Route::get('/technician/{technicianId}', [ReviewController::class, 'indexByTechnician']);
        Route::get('/user', [ReviewController::class, 'indexByUser']);
        Route::delete('/{id}', [ReviewController::class, 'destroy']);
    });
});

// Rutas de salud/estado del API
Route::get('/health', function() {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});