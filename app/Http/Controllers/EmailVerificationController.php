<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

class EmailVerificationController extends Controller
{
    /**
     * Verificar email mediante URL web (para cuando el usuario hace clic en el email)
     */
    public function verifyWeb(Request $request, $id, $hash)
    {
        try {
            $user = User::findOrFail($id);
            
            \Log::info("Verificando email para usuario: {$user->email}");
            
            if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
                \Log::error("Hash inválido para usuario {$user->id}");
                return $this->showResult(false, 'Enlace de verificación inválido', 'Error de Verificación');
            }

            if ($user->hasVerifiedEmail()) {
                \Log::info("Email ya verificado para usuario {$user->id}");
                
                // ✅ ASEGURAR QUE ESTÉ ACTIVO AUNQUE YA ESTÉ VERIFICADO
                if (!$user->is_active) {
                    $user->update(['is_active' => true]);
                    \Log::info("Usuario activado: {$user->id}");
                }
                
                return $this->showResult(true, 'Tu email ya estaba verificado y tu cuenta está activa', 'Email Ya Verificado', true);
            }

            // ✅ MARCAR COMO VERIFICADO Y ACTIVAR USUARIO
            if ($user->markEmailAsVerified()) {
                $user->update(['is_active' => true]); // ← ESTA ES LA LÍNEA QUE FALTABA
                \Log::info("Email verificado y usuario activado: {$user->id}");
                
                event(new Verified($user));
                
                return $this->showResult(true, '¡Tu email ha sido verificado exitosamente y tu cuenta está activa! Ya puedes iniciar sesión.', '¡Verificación Exitosa!', true);
            }

            return $this->showResult(false, 'Error al verificar el email. Intenta de nuevo.', 'Error de Verificación');

        } catch (\Exception $e) {
            \Log::error("Error en verificación: " . $e->getMessage());
            return $this->showResult(false, 'Ha ocurrido un error inesperado', 'Error');
        }
    }

    /**
     * Mostrar resultado de verificación con página HTML bonita
     */
    private function showResult($success, $message, $title, $showLoginButton = false)
    {
        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$title} - ConectaPro</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0;
                }
                .card {
                    background: white;
                    padding: 40px;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                    text-align: center;
                    max-width: 500px;
                }
                .success { color: #28a745; font-size: 3rem; }
                .error { color: #dc3545; font-size: 3rem; }
                .btn {
                    background: #667eea;
                    color: white;
                    padding: 12px 30px;
                    text-decoration: none;
                    border-radius: 25px;
                    display: inline-block;
                    margin-top: 20px;
                }
                .btn:hover { background: #5a67d8; color: white; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class='card'>
                <h1>🔧 ConectaPro</h1>
        ";
        
        if ($success) {
            $html .= "<div class='success'>✅</div><h2 style='color: #28a745;'>{$title}</h2>";
        } else {
            $html .= "<div class='error'>❌</div><h2 style='color: #dc3545;'>{$title}</h2>";
        }
        
        $html .= "<p>{$message}</p>";
        
        if ($showLoginButton) {
            $html .= "<a href='/login' class='btn'>🔑 Iniciar Sesión</a>";
        }
        
        $html .= "
            </div>
        </body>
        </html>";
        
        return response($html);
    }

    // ✅ CORREGIR TAMBIÉN EL MÉTODO verify() PARA API
    public function verify(Request $request): JsonResponse
    {
        try {
            $user = User::find($request->route('id'));
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            if ($user->hasVerifiedEmail()) {
                // Asegurar que esté activo
                if (!$user->is_active) {
                    $user->update(['is_active' => true]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Email ya está verificado',
                    'redirect' => '/login'
                ]);
            }

            if ($user->markEmailAsVerified()) {
                $user->update(['is_active' => true]); // ← ACTIVAR USUARIO
                event(new Verified($user));
                
                return response()->json([
                    'success' => true,
                    'message' => '¡Email verificado exitosamente! Ya puedes iniciar sesión.',
                    'redirect' => '/login'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el email'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la verificación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar email de verificación
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email ya está verificado'
                ]);
            }

            $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Email de verificación enviado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar email de verificación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reenviar email de verificación
     */
    public function resend(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);

            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El email ya está verificado'
                ], 400);
            }

            $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Email de verificación reenviado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reenviar email: ' . $e->getMessage()
            ], 500);
        }
    }
}