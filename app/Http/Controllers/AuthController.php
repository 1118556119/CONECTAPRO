<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TechnicianProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageHelper;
use Exception;

class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario en el sistema.
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Validación
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'idNumber' => 'required|string|max:20|unique:users,idNumber',
                'phone' => 'required|string|max:20',
                'gender' => 'required|in:male,female,other',
                'birthDate' => 'required|date|before:today',
                'user_type' => 'required|in:client,technician',
                'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,bmp,svg,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Procesar foto si existe
            $photoPath = null;
            if ($request->hasFile('photo')) {
                try {
                    if (extension_loaded('gd')) {
                        $photoPath = ImageHelper::processAndResizeProfilePhoto($request->file('photo'));
                    } else {
                        $photoPath = ImageHelper::processProfilePhoto($request->file('photo'));
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al procesar la imagen: ' . $e->getMessage()
                    ], 400);
                }
            }

            // Crear usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'idNumber' => $request->idNumber,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'birthDate' => $request->birthDate,
                'user_type' => $request->user_type,
                'photo' => $photoPath,
                'is_active' => false, // ← CAMBIAR A FALSE hasta verificar email
                'email_verified_at' => null, // ← Email no verificado inicialmente
            ]);

            // Si es técnico, crear perfil técnico
            if ($request->user_type === 'technician') {
                TechnicianProfile::create([
                    'user_id' => $user->id,
                    'bio' => null,
                    'experience_years' => 0,
                    'hourly_rate' => null,
                    'is_verified' => false,
                    'is_available' => true,
                    'profile_completeness' => $this->calculateInitialCompleteness($request),
                ]);
            }

            // ENVIAR EMAIL DE VERIFICACIÓN
            $user->sendEmailVerificationNotification();

            // NO CREAR TOKEN AÚN - el usuario debe verificar primero

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente. Por favor verifica tu email antes de iniciar sesión.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'user_type' => $user->user_type,
                        'email_verified' => false,
                    ],
                    'requires_verification' => true
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error en registro: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Inicia sesión de un usuario existente.
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas',
                ], 401);
            }

            // VERIFICAR SI EL EMAIL ESTÁ VERIFICADO
            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes verificar tu email antes de iniciar sesión',
                    'requires_verification' => true,
                    'user_email' => $user->email
                ], 403);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cuenta desactivada',
                ], 403);
            }

            // Actualizar último login
            $user->update(['last_login_at' => now()]);

            // Generar token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Cargar relaciones
            $userWithProfile = $user->load('technicianProfile');

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'user' => $userWithProfile,
                    'token' => $token,
                    'user_type' => $userWithProfile->user_type,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en login: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
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
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            $user = $request->user()->load('technicianProfile');
            
            \Log::info('Datos del usuario solicitados:', $user->toArray());
            
            return response()->json([
                'success' => true,
                'data' => $user
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener usuario: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function refresh(Request $request)
    {
        try {
            $user = $request->user();
            
            // Eliminar token actual
            $request->user()->currentAccessToken()->delete();
            
            // Crear nuevo token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'user' => $user->load('technicianProfile'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al renovar token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener perfil del usuario autenticado
     */
    public function profile(Request $request): JsonResponse // ← ESTE ES EL TIPO CORRECTO
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Cargar relaciones necesarias
            $user->load(['technicianProfile']);

            // Estructurar los datos del perfil
            $profileData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'idNumber' => $user->idNumber ?? '', // ← USAR LOS NOMBRES CORRECTOS DE CAMPOS
                'phone' => $user->phone ?? '',
                'birthDate' => $user->birthDate ?? '',
                'gender' => $user->gender ?? '',
                'address' => $user->address ?? '',
                'city' => $user->city ?? '',
                'postal_code' => $user->postal_code ?? '',
                'photo' => $user->photo ?? '',
                'user_type' => $user->user_type ?? 'client',
                'is_active' => $user->is_active ?? true,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                
                // Arrays vacíos por ahora (hasta que tengas las tablas)
                'education' => [],
                'experience' => [],
                
                // Stats por defecto
                'rating_average' => 0,
                'total_jobs' => 0
            ];

            // Si es técnico y tiene perfil técnico, agregar esos datos
            if ($user->user_type === 'technician' && $user->technicianProfile) {
                $techProfile = $user->technicianProfile;
                
                $profileData = array_merge($profileData, [
                    'specialty' => $techProfile->specialty,
                    'years_of_experience' => $techProfile->experience_years ?? 0,
                    'bio' => $techProfile->bio,
                    'education_level' => $techProfile->education_level,
                    'institution' => $techProfile->institution,
                    'degree' => $techProfile->degree,
                    'graduation_year' => $techProfile->graduation_year,
                    'certifications' => $techProfile->certifications ?? [],
                    'skills' => $techProfile->skills ?? [],
                    'languages' => $techProfile->languages ?? [],
                    'current_company' => $techProfile->current_company,
                    'current_position' => $techProfile->current_position,
                    'hourly_rate' => $techProfile->hourly_rate ?? 0,
                    'is_verified' => $techProfile->is_verified ?? false,
                    'is_available' => $techProfile->is_available ?? true,
                    'service_areas' => $techProfile->service_areas ?? [],
                    'availability_schedule' => $techProfile->availability_schedule ?? [],
                    'profile_completeness' => $techProfile->profile_completeness ?? 20,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $profileData,
                'message' => 'Perfil obtenido correctamente'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener perfil:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $request->user()->id ?? 'N/A'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar perfil del usuario autenticado
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $section = $request->input('section', 'personal');

            switch ($section) {
                case 'personal':
                    return $this->updatePersonalInfo($request, $user);
                
                case 'education_add':
                case 'education_update':
                case 'education_remove':
                    return $this->handleEducationUpdate($request, $user, $section);
                
                case 'experience_add':
                case 'experience_update':
                case 'experience_remove':
                    return $this->handleExperienceUpdate($request, $user, $section);
                
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Sección no válida'
                    ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Error al actualizar perfil:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $request->user()->id ?? 'N/A'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar información personal
     */
    private function updatePersonalInfo(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'idNumber' => 'required|string|max:20|unique:users,id_number,' . $user->id,
            'phone' => 'required|string|max:20',
            'birthDate' => 'nullable|date',
            'gender' => 'nullable|in:Masculino,Femenino,Otro',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Actualizar campos básicos
        $user->name = $request->input('name');
        $user->id_number = $request->input('idNumber');
        $user->phone = $request->input('phone');
        $user->birth_date = $request->input('birthDate');
        $user->gender = $request->input('gender');
        $user->address = $request->input('address');
        $user->city = $request->input('city');
        $user->postal_code = $request->input('postal_code');

        // Manejar foto de perfil
        if ($request->hasFile('photo')) {
            // Eliminar foto anterior si existe
            if ($user->photo) {
                $oldPhotoPath = str_replace('/storage/', '', $user->photo);
                Storage::disk('public')->delete($oldPhotoPath);
            }

            // Guardar nueva foto
            $photoPath = $request->file('photo')->store('profile-photos', 'public');
            $user->photo_url = '/storage/' . $photoPath;
        }

        $user->save();

        // Devolver datos actualizados
        $profileData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'idNumber' => $user->id_number,
            'phone' => $user->phone,
            'birthDate' => $user->birth_date,
            'gender' => $user->gender,
            'address' => $user->address,
            'city' => $user->city,
            'postal_code' => $user->postal_code,
            'photo' => $user->photo,
            'user_type' => $user->user_type,
            'education' => [],
            'experience' => []
        ];

        return response()->json([
            'success' => true,
            'data' => $profileData,
            'message' => 'Información personal actualizada correctamente'
        ]);
    }

    /**
     * Manejar actualizaciones de educación
     */
    private function handleEducationUpdate(Request $request, User $user, string $section): JsonResponse
    {
        // Por ahora, simular que se guardó correctamente
        // Aquí implementarías la lógica real cuando tengas la tabla de educación
        
        $educationData = [];
        
        if ($section === 'education_add') {
            // Simular agregar nueva educación
            $educationData[] = [
                'id' => rand(1000, 9999),
                'education_level' => $request->input('education_level'),
                'institution' => $request->input('institution'),
                'degree' => $request->input('degree'),
                'graduation_year' => $request->input('graduation_year'),
                'certificate_url' => null
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'education' => $educationData
            ],
            'message' => 'Educación actualizada correctamente'
        ]);
    }

    /**
     * Manejar actualizaciones de experiencia
     */
    private function handleExperienceUpdate(Request $request, User $user, string $section): JsonResponse
    {
        // Por ahora, simular que se guardó correctamente
        // Aquí implementarías la lógica real cuando tengas la tabla de experiencia
        
        $experienceData = [];
        
        if ($section === 'experience_add') {
            // Simular agregar nueva experiencia
            $experienceData[] = [
                'id' => rand(1000, 9999),
                'company_name' => $request->input('company_name'),
                'position' => $request->input('position'),
                'supervisor_name' => $request->input('supervisor_name'),
                'supervisor_phone' => $request->input('supervisor_phone'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'currently_working' => $request->input('currently_working') === '1',
                'activities' => $request->input('activities'),
                'certification_url' => null
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'experience' => $experienceData
            ],
            'message' => 'Experiencia actualizada correctamente'
        ]);
    }

    // Actualizar el método de cálculo de completitud inicial
    private function calculateInitialCompleteness($request)
    {
        $completeness = 20; // Base por registro
        
        if ($request->phone) $completeness += 10;
        if ($request->birthDate) $completeness += 10;
        if ($request->gender) $completeness += 10;
        
        return min($completeness, 100);
    }
}