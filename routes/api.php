<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TechnicianProfileController;
use App\Http\Controllers\EmailVerificationController;


// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas de verificación de email
Route::post('/email/resend', [EmailVerificationController::class, 'resend']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // Profile (rutas adicionales)
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile', [ProfileController::class, 'update']); // Para FormData
    
    // Rutas de solicitudes de servicio
    Route::prefix('service-requests')->group(function () {
        // Rutas para clientes
        Route::get('/', [ServiceRequestController::class, 'index']);
        Route::post('/', [ServiceRequestController::class, 'store']);
        Route::get('/{id}', [ServiceRequestController::class, 'show']);
        Route::post('/{id}/accept-quote', [ServiceRequestController::class, 'acceptQuote']);
        Route::post('/{id}/reject-quote', [ServiceRequestController::class, 'rejectQuote']);
        Route::post('/{id}/cancel', [ServiceRequestController::class, 'cancelRequest']);
        
        // Rutas para técnicos
        Route::get('/available/list', [ServiceRequestController::class, 'availableRequests']);
        Route::get('/my-assigned/list', [ServiceRequestController::class, 'myAssignedRequests']); // NUEVA RUTA
        Route::post('/{id}/apply', [ServiceRequestController::class, 'applyToRequest']);
        Route::post('/{id}/start', [ServiceRequestController::class, 'startWork']);
        Route::post('/{id}/complete', [ServiceRequestController::class, 'completeWork']);
    });
    
    // Ruta para ver perfil de técnico
    Route::get('/technicians/{id}/profile', [TechnicianProfileController::class, 'show']);
    
    // Rutas para reseñas
    Route::prefix('reviews')->group(function () {
        Route::post('/', [ReviewController::class, 'store']);
        Route::get('/technician/{id}', [ReviewController::class, 'indexByTechnician']);
        Route::get('/my-reviews', [ReviewController::class, 'indexByUser']);
        Route::delete('/{id}', [ReviewController::class, 'destroy']);
    });
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    
    // Verificación de email
    Route::post('/email/verify', [EmailVerificationController::class, 'sendVerificationEmail']);
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify']);
});

// Rutas de salud/estado del API
Route::get('/health', function() {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// Agregar estas rutas con el middleware 'auth:api'
Route::middleware('auth:sanctum')->group(function () {
    // Rutas existentes...
    
    // Notificaciones
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});