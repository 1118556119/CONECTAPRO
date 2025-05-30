<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TechnicianProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ProfileController extends Controller
{
    /**
     * Muestra el perfil del usuario autenticado
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        try {
            $user = Auth::user()->load('technicianProfile');
            
            // Respuesta base
            $response = [
                'name' => $user->name,
                'idNumber' => $user->idNumber,
                'phone' => $user->phone,
                'birthDate' => $user->birthDate,
                'gender' => $user->gender,
                'email' => $user->email,
                'photo_url' => $user->photo_url,
            ];

            // Si es técnico, añadir datos del perfil técnico
            if ($user->user_type === 'technician' && $user->technicianProfile) {
                $techProfile = $user->technicianProfile;
                
                $response = array_merge($response, [
                    // Datos profesionales
                    'specialty' => $techProfile->specialty ?? '',
                    'experience' => $techProfile->experience ?? 0,
                    'bio' => $techProfile->bio ?? '',
                    'current_company' => $techProfile->current_company ?? '',
                    'position' => $techProfile->position ?? '',
                    'verified' => $techProfile->verified ?? false,
                    'available' => $techProfile->available ?? true,
                    
                    // Formación académica - campos individuales para compatibilidad
                    'education_level' => $techProfile->education_level ?? '',
                    'education' => $techProfile->education ?? '',
                    'institution' => $techProfile->institution ?? '',
                    'graduation_year' => $techProfile->graduation_year ?? '',
                    'start_year' => $techProfile->start_year ?? '',
                    'education_status' => $techProfile->education_status ?? '',
                    'thesis_title' => $techProfile->thesis_title ?? '',
                    'education_country' => $techProfile->education_country ?? '',
                    'education_city' => $techProfile->education_city ?? '',
                    
                    // Otros estudios - campos individuales para compatibilidad
                    'certifications' => $techProfile->certifications ?? '',
                    'languages' => $techProfile->languages ?? '',
                    'additional_courses' => $techProfile->additional_courses ?? '',
                    
                    // NUEVO: Arrays estructurados para los elementos múltiples
                    'educationItems' => $this->buildEducationItems($techProfile),
                    'experienceItems' => $this->buildExperienceItems($techProfile),
                    'additionalItems' => $this->buildAdditionalItems($techProfile),
                    
                    // Información de negocios
                    'hourly_rate' => $techProfile->hourly_rate ?? 0,
                    'service_area' => $techProfile->service_area ?? '',
                    'availability_schedule' => $techProfile->availability_schedule ?? [],
                    
                    // Metadatos
                    'profile_completeness' => $techProfile->getCompletenessPercentage(),
                ]);
            }
            
            // Añadir timestamp de actualización
            $response['updated_at'] = $user->updated_at->toDateTimeString();
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener perfil de usuario: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error al cargar el perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza el perfil del usuario
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            Log::debug('Actualizando perfil - Datos recibidos:', $request->except(['photo']));
            
            $user = Auth::user();
            
            // Modificar validación según el tipo de usuario
            $baseValidation = [
                // Datos personales comunes
                'name' => 'sometimes|string|max:255',
                'idNumber' => 'sometimes|string|max:20|unique:users,idNumber,' . $user->id,
                'phone' => 'sometimes|string|max:20',
                'birthDate' => 'sometimes|date',
                'gender' => 'sometimes|string|in:Masculino,Femenino,Otro',
                'photo' => 'sometimes|image|max:2048', // 2MB máximo
            ];
            
            // Añadir reglas de validación específicas para técnicos
            if ($user->user_type === 'technician') {
                $technicianRules = [
                    'specialty' => 'sometimes|string|max:255',
                    'experience' => 'sometimes|integer|min:0|max:70',
                    'bio' => 'sometimes|string|max:1000',
                    'current_company' => 'sometimes|string|max:255',
                    'position' => 'sometimes|string|max:255',
                    'available' => 'sometimes|boolean',
                    
                    // Formación académica
                    'education_level' => 'sometimes|string|max:100',
                    'education' => 'sometimes|string|max:255',
                    'institution' => 'sometimes|string|max:255',
                    'graduation_year' => 'sometimes|integer|min:1950|max:' . (date('Y') + 10),
                    'start_year' => 'sometimes|integer|min:1950|max:' . date('Y'),
                    'education_status' => 'sometimes|string|in:Completado,En curso,Abandonado,Suspendido',
                    'thesis_title' => 'sometimes|string|max:255',
                    'education_country' => 'sometimes|string|max:100',
                    'education_city' => 'sometimes|string|max:100',
                    
                    // Otros estudios
                    'certifications' => 'sometimes|string|max:500',
                    'languages' => 'sometimes|string|max:500',
                    'additional_courses' => 'sometimes|string|max:1000',
                    
                    // Información de negocios
                    'hourly_rate' => 'sometimes|numeric|min:0',
                    'service_area' => 'sometimes|string|max:255',
                    'availability_schedule' => 'sometimes|array',
                ];
                
                $baseValidation = array_merge($baseValidation, $technicianRules);
            }
            
            // Validar datos
            $validator = Validator::make($request->all(), $baseValidation);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Actualizar campos del usuario común
            $userFields = ['name', 'idNumber', 'phone', 'gender', 'email'];
            $changedUserFields = [];
            
            foreach ($userFields as $field) {
                if ($request->has($field) && $request->$field !== null) {
                    $user->$field = $request->$field;
                    $changedUserFields[] = $field;
                }
            }
            
            // Manejo especial para fechas
            if ($request->has('birthDate') && $request->birthDate !== null) {
                try {
                    $birthDate = Carbon::parse($request->birthDate)->format('Y-m-d');
                    $user->birthDate = $birthDate;
                    $changedUserFields[] = 'birthDate';
                } catch (\Exception $e) {
                    Log::error('Error al procesar fecha de nacimiento: ' . $e->getMessage());
                }
            }
            
            // Guardar usuario si hubo cambios
            if (!empty($changedUserFields)) {
                $user->save();
                Log::debug('Usuario actualizado - Campos: ' . implode(', ', $changedUserFields));
            }

            // Si es técnico, actualizar el perfil técnico
            if ($user->user_type === 'technician') {
                $techProfile = $user->technicianProfile;
                if (!$techProfile) {
                    $techProfile = new TechnicianProfile();
                    $techProfile->user_id = $user->id;
                    Log::info('Creando nuevo perfil técnico para usuario ID: ' . $user->id);
                }

                // Campos del perfil técnico a actualizar
                $techFields = [
                    'specialty', 'experience', 'bio', 'current_company', 'position',
                    'education_level', 'education', 'institution', 'graduation_year',
                    'start_year', 'education_status', 'thesis_title', 'education_country',
                    'education_city', 'certifications', 'languages', 'additional_courses',
                    'available', 'hourly_rate', 'service_area', 'availability_schedule'
                ];
                
                $changedTechFields = [];
                
                // Actualizar cada campo del perfil técnico
                foreach ($techFields as $field) {
                    if ($request->has($field) && $request->$field !== null) {
                        $techProfile->$field = $request->$field;
                        $changedTechFields[] = $field;
                    }
                }

                // Guardar perfil técnico si hubo cambios
                if (!empty($changedTechFields)) {
                    $techProfile->save();
                    Log::debug('Perfil técnico actualizado - Campos: ' . implode(', ', $changedTechFields));
                }
            }

            // Procesar foto si se proporciona una nueva
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                // Eliminar foto anterior si existe
                if ($user->photo_url && Storage::disk('public')->exists($user->photo_url)) {
                    Storage::disk('public')->delete($user->photo_url);
                }

                // Almacenar nueva foto
                $photoPath = $request->file('photo')->store('profile_photos', 'public');
                $user->photo_url = $photoPath;
                $user->save();
                
                Log::info('Foto de perfil actualizada: ' . $photoPath);
            }

            // Recargar usuario y preparar respuesta
            $user = $user->fresh()->load('technicianProfile');
            
            // Construir la respuesta con datos actualizados
            $response = [
                'name' => $user->name,
                'idNumber' => $user->idNumber,
                'phone' => $user->phone,
                'birthDate' => $user->birthDate,
                'gender' => $user->gender,
                'email' => $user->email,
                'photo_url' => $user->photo_url,
            ];

            if ($user->user_type === 'technician' && $user->technicianProfile) {
                $techProfile = $user->technicianProfile;
                
                $response = array_merge($response, [
                    'specialty' => $techProfile->specialty ?? '',
                    'experience' => $techProfile->experience ?? 0,
                    'bio' => $techProfile->bio ?? '',
                    'current_company' => $techProfile->current_company ?? '',
                    'position' => $techProfile->position ?? '',
                    'education_level' => $techProfile->education_level ?? '',
                    'education' => $techProfile->education ?? '',
                    'institution' => $techProfile->institution ?? '',
                    'graduation_year' => $techProfile->graduation_year ?? '',
                    'start_year' => $techProfile->start_year ?? '',
                    'education_status' => $techProfile->education_status ?? '',
                    'thesis_title' => $techProfile->thesis_title ?? '',
                    'education_country' => $techProfile->education_country ?? '',
                    'education_city' => $techProfile->education_city ?? '',
                    'certifications' => $techProfile->certifications ?? '',
                    'languages' => $techProfile->languages ?? '',
                    'additional_courses' => $techProfile->additional_courses ?? '',
                    
                    // IMPORTANTE: Incluir los arrays actualizados
                    'educationItems' => $this->buildEducationItems($techProfile),
                    'experienceItems' => $this->buildExperienceItems($techProfile),
                    'additionalItems' => $this->buildAdditionalItems($techProfile),
                ]);
            }

            $response['message'] = 'Perfil actualizado correctamente';
            $response['updated_at'] = $user->updated_at->toDateTimeString();
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar perfil: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->except(['photo'])
            ]);
            
            return response()->json([
                'message' => 'Error al actualizar el perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Construye los elementos de educación desde los datos del perfil
     */
    private function buildEducationItems($techProfile)
    {
        $items = [];
        
        // Si hay datos de educación, crear al menos un elemento
        if ($techProfile->education || $techProfile->education_level) {
            $items[] = [
                'education' => $techProfile->education ?? '',
                'education_level' => $techProfile->education_level ?? '',
                'institution' => $techProfile->institution ?? '',
                'graduation_year' => $techProfile->graduation_year,
                'start_year' => $techProfile->start_year,
                'education_status' => $techProfile->education_status ?? '',
                'thesis_title' => $techProfile->thesis_title,
                'education_country' => $techProfile->education_country,
                'education_city' => $techProfile->education_city,
            ];
        }
        
        return $items;
    }

    /**
     * Construye los elementos de experiencia desde los datos del perfil
     */
    private function buildExperienceItems($techProfile)
    {
        $items = [];
        
        // Si hay datos de experiencia, crear al menos un elemento
        if ($techProfile->current_company || $techProfile->position) {
            $items[] = [
                'current_company' => $techProfile->current_company ?? '',
                'position' => $techProfile->position ?? '',
                'job_start_date' => '', // Estas fechas no están en la DB actual
                'job_end_date' => '',
                'is_current_job' => true,
                'experience' => $techProfile->experience ?? '',
                'industry_sector' => '', // Este campo no está en la DB actual
                'bio' => $techProfile->bio ?? '',
                'job_description' => $techProfile->bio ?? '',
                'job_responsibilities' => '',
                'job_location' => ''
            ];
        }
        
        return $items;
    }

    /**
     * Construye los elementos adicionales desde los datos del perfil
     */
    private function buildAdditionalItems($techProfile)
    {
        $items = [];
        
        // Certificaciones
        if ($techProfile->certifications) {
            $items[] = [
                'type' => 'Certificación',
                'title' => $techProfile->certifications,
                'institution' => '',
                'date' => '',
                'level' => '',
                'hours' => null,
                'description' => ''
            ];
        }
        
        // Idiomas
        if ($techProfile->languages) {
            $items[] = [
                'type' => 'Idioma',
                'title' => $techProfile->languages,
                'institution' => '',
                'date' => '',
                'level' => '',
                'hours' => null,
                'description' => ''
            ];
        }
        
        // Cursos adicionales
        if ($techProfile->additional_courses) {
            $items[] = [
                'type' => 'Curso',
                'title' => $techProfile->additional_courses,
                'institution' => '',
                'date' => '',
                'level' => '',
                'hours' => null,
                'description' => ''
            ];
        }
        
        return $items;
    }
}