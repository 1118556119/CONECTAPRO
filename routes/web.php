<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailVerificationController;

// 🔧 RUTAS ESPECÍFICAS PRIMERO (antes del catch-all)

// Ruta para verificar email desde el enlace en el correo
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyWeb'])
    ->middleware(['signed'])
    ->name('verification.verify');

// Ruta de prueba para el correo
Route::get('/test-email', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('¡El correo de ConectaPro funciona! 🎉', function ($message) {
            $message->to('appejemplo6119@gmail.com')
                    ->subject('Prueba ConectaPro - ' . now()->format('d/m/Y H:i:s'));
        });
        
        return '<h2>✅ Correo enviado correctamente!</h2><p>Revisa tu bandeja de entrada.</p>';
    } catch (Exception $e) {
        return '<h2>❌ Error:</h2><p>' . $e->getMessage() . '</p>';
    }
});

// Ruta de prueba para verificar manualmente
Route::get('/test-verify/{userId}', function($userId) {
    $user = \App\Models\User::findOrFail($userId);
    
    if ($user->hasVerifiedEmail()) {
        return "<h2>✅ Email ya verificado</h2><p>Usuario: {$user->name}<br>Email: {$user->email}<br>Verificado: {$user->email_verified_at}</p>";
    }
    
    $user->markEmailAsVerified();
    $user->update(['is_active' => true]);
    
    return "<h2>✅ Email verificado exitosamente</h2><p>Usuario: {$user->name}<br>Email: {$user->email}</p><a href='/login'>Ir a Login</a>";
});

// API routes prefix
Route::prefix('api')->group(function () {
    // Las rutas API se definen en routes/api.php
});

// 🔧 CATCH-ALL AL FINAL (para que no intercepte las rutas específicas)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');