<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * Controlador para manejar la autenticación de usuarios (registro, inicio de sesión y cierre de sesión).
 * Este controlador utiliza Laravel Sanctum para la generación de tokens de autenticación.
 */
class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario en el sistema.
     *
     * @param Request $request Contiene los datos enviados desde el formulario de registro (name, idNumber, phone, birthDate, gender, email, password).
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el token de autenticación y los datos del usuario, o un error si falla.
     */
    public function register(Request $request)
    {
        try {
            // Validación de los datos recibidos del formulario
            $validatedData = $request->validate([
                'name' => 'required|string|max:255', // Nombre completo del usuario
                'idNumber' => 'required|string|unique:users', // Número de cédula único
                'phone' => 'required|string|max:20', // Número de teléfono
                'birthDate' => 'required|date', // Fecha de nacimiento
                'gender' => 'required|string|in:Masculino,Femenino,Otro', // Género (valores permitidos)
                'email' => 'required|string|email|max:255|unique:users', // Correo electrónico único
                'password' => 'required|string|min:8|confirmed', // Contraseña con confirmación
                'user_type' => 'required|string|in:client,technician', // Validamos el tipo de usuario
                
                // Campos específicos para técnicos
                'specialty' => 'required_if:user_type,technician|string|nullable|max:255',
                'experience' => 'required_if:user_type,technician|integer|nullable|min:0|max:70',
            ]);

            // Creación del nuevo usuario en la base de datos
            $user = User::create([
                'name' => $validatedData['name'],
                'idNumber' => $validatedData['idNumber'],
                'phone' => $validatedData['phone'],
                'birthDate' => $validatedData['birthDate'],
                'gender' => $validatedData['gender'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
                'user_type' => $validatedData['user_type'], // Guardamos el tipo seleccionado
            ]);

            // Creación del perfil de técnico asociado al usuario
            $techProfileData = [
                'specialty' => $request->user_type === 'technician' ? $request->specialty : null,
                'experience' => $request->user_type === 'technician' ? $request->experience : null,
                'bio' => null,
                'photo_url' => null,
            ];
            
            // Solo añadimos campos específicos para técnicos si el usuario es técnico
            $user->technicianProfile()->create($techProfileData);

            // Generación del token de autenticación usando Laravel Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            // Retorno de la respuesta JSON con el token y los datos del usuario
            return response()->json([
                'token' => $token,
                'user' => $user->load('technicianProfile'),
                'user_type' => $validatedData['user_type'],
                'message' => 'Usuario registrado exitosamente'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Manejo de errores de validación
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Illuminate\Database\QueryException $e) {
            // Manejo de errores de base de datos (por ejemplo, violación de unicidad)
            return response()->json([
                'message' => 'Error de base de datos',
                'error' => $e->getMessage(),
            ], 500);

        } catch (\Exception $e) {
            // Manejo de errores genéricos
            return response()->json([
                'message' => 'Error al registrar el usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Inicia sesión de un usuario existente.
     *
     * @param Request $request Contiene las credenciales (email y password) del usuario.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el token y datos del usuario
     */
    public function login(Request $request)
    {
        try {
            // Validación de las credenciales
            $credentials = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // Intento de autenticación
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Credenciales incorrectas',
                ], 401);
            }

            // Obtención del usuario autenticado y generación del token
            $user = Auth::user()->load('technicianProfile');
            $token = $user->createToken('auth_token')->plainTextToken;

            // Retorno de la respuesta JSON con el token y los datos del usuario
            return response()->json([
                'token' => $token,
                'user' => $user,
                'user_type' => $user->user_type,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Manejo de errores de validación
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Manejo de errores genéricos
            return response()->json([
                'message' => 'Error al iniciar sesión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cierra la sesión del usuario actual.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con un mensaje de éxito
     */
    public function logout()
    {
        try {
            // Eliminación de todos los tokens del usuario autenticado
            auth()->user()->tokens()->delete();

            // Retorno de la respuesta JSON con mensaje de éxito
            return response()->json([
                'message' => 'Sesión cerrada',
            ]);
        } catch (\Exception $e) {
            // Manejo de errores genéricos
            return response()->json([
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Método para inicio de sesión por OAuth (Google, Microsoft, Facebook)
     * 
     * @param Request $request
     * @param string $provider Nombre del proveedor (google, microsoft, facebook)
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function redirectToProvider(Request $request, $provider)
    {
        // Validar que se ha enviado el tipo de usuario
        if (!$request->has('user_type')) {
            return response()->json([
                'message' => 'Debes seleccionar un tipo de usuario (cliente o técnico)',
            ], 400);
        }
        
        // Validar que el tipo de usuario es válido
        $userType = $request->user_type;
        if (!in_array($userType, ['client', 'technician'])) {
            return response()->json([
                'message' => 'El tipo de usuario debe ser cliente o técnico',
            ], 400);
        }
        
        // Guardar el tipo de usuario en la sesión para usarlo después de la autenticación
        session(['oauth_user_type' => $userType]);
        
        // Aquí implementarías la redirección al proveedor de OAuth
        // (Requiere configurar Laravel Socialite u otra biblioteca de OAuth)
        // return Socialite::driver($provider)->redirect();
    }
}