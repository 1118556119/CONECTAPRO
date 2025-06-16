<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TechnicianProfile;
use App\Models\UserEducation;
use App\Models\UserExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ImageHelper;

class ProfileController extends Controller
{
    /**
     * Muestra el perfil del usuario autenticado
     */
    public function show()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Cargar educación y experiencia del usuario
            $education = UserEducation::where('user_id', $user->id)
                ->orderBy('graduation_year', 'desc')
                ->get();
                
            $experience = UserExperience::where('user_id', $user->id)
                ->orderBy('start_date', 'desc')
                ->get();

            // Obtener perfil técnico si existe
            $technicianProfile = null;
            if ($user->user_type === 'technician') {
                $technicianProfile = TechnicianProfile::where('user_id', $user->id)->first();
            }

            $userData = $user->toArray();
            $userData['education'] = $education;
            $userData['experience'] = $experience;
            
            if ($technicianProfile) {
                $userData['technician_profile'] = $technicianProfile;
                $userData['rating_average'] = $technicianProfile->rating_average ?? 0;
                $userData['total_jobs'] = $technicianProfile->total_jobs ?? 0;
            }

            return response()->json([
                'success' => true,
                'data' => $userData,
                'message' => 'Perfil obtenido correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza el perfil del usuario
     */
    public function update(Request $request)
    {
        try {
            $user = Auth::user();
            $section = $request->input('section', 'basic');
            $action = $request->input('action', 'add');
            
            Log::info('Profile update request:', [
                'section' => $section,
                'action' => $action,
                'user_id' => $user->id,
                'all_data' => $request->all()
            ]);
            
            if ($section === 'experience') {
                return $this->handleExperience($request, $user, $action);
            }
            
            if ($section === 'education') {
                return $this->handleEducation($request, $user, $action);
            }
            
            // Para otros casos
            return $this->updateBasicInfo($request, $user);
            
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }

    private function handleExperience(Request $request, $user, $action)
    {
        try {
            // Los datos vienen directamente en el request, no en 'data'
            $data = [
                'company_name' => $request->input('company_name'),
                'position' => $request->input('position'),
                'employment_type' => $request->input('employment_type'),
                'supervisor_name' => $request->input('supervisor_name'),
                'supervisor_phone' => $request->input('supervisor_phone'),
                'supervisor_email' => $request->input('supervisor_email'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'currently_working' => $request->input('currently_working', false),
                'activities' => $request->input('activities', ''), // ← Valor por defecto
                'achievements' => $request->input('achievements'),
                'skills_used' => $request->input('skills_used'),
                'technologies_used' => $request->input('technologies_used'),
                'industry_sector' => $request->input('industry_sector'),
                'company_size' => $request->input('company_size'),
                'salary' => $request->input('salary'),
                'currency' => $request->input('currency'),
                'departure_reason' => $request->input('departure_reason'),
                'work_city' => $request->input('work_city') ?? $request->input('city'),
                'work_country' => $request->input('work_country') ?? $request->input('country', 'Colombia'),
                'is_remote' => $request->input('is_remote', false),
                'is_featured' => $request->input('is_featured', false),
                'allow_contact' => $request->input('allow_contact', true),
            ];
            
            Log::info('Experience data processed:', [
                'action' => $action,
                'data' => $data
            ]);
            
            // Validar datos mínimos
            if (empty($data['company_name']) || empty($data['position']) || empty($data['start_date'])) {
                return response()->json([
                    'error' => 'Faltan datos obligatorios: empresa, cargo y fecha de inicio son requeridos',
                    'received_data' => $data
                ], 422);
            }
            
            // Manejar según la acción
            switch ($action) {
                case 'add':
                    return $this->createExperience($data, $user, $request);
                
                case 'edit':
                    $index = $request->input('index');
                    return $this->updateExperienceByIndex($data, $user, $request, $index);
                
                case 'delete':
                    $index = $request->input('index');
                    return $this->deleteExperience($user, $index);
                
                default:
                    return $this->createExperience($data, $user, $request);
            }
            
        } catch (\Exception $e) {
            Log::error('Error handling experience: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar experiencia: ' . $e->getMessage()], 500);
        }
    }

    private function createExperience($data, $user, $request)
    {
        DB::beginTransaction();
        
        try {
            // Formatear fechas
            if (isset($data['start_date']) && strlen($data['start_date']) > 10) {
                $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
            }
            
            if (isset($data['end_date']) && strlen($data['end_date']) > 10) {
                $data['end_date'] = date('Y-m-d', strtotime($data['end_date']));
            }
            
            // Si currently_working es true, end_date debe ser null
            if ($data['currently_working'] == '1' || $data['currently_working'] === true) {
                $data['end_date'] = null;
                $data['currently_working'] = true;
            } else {
                $data['currently_working'] = false;
            }
            
            // Filtrar valores undefined o null problemáticos
            foreach ($data as $key => $value) {
                if ($value === 'undefined' || $value === 'null') {
                    $data[$key] = null;
                }
            }
            
            // Asegurar que activities tenga un valor
            if (empty($data['activities']) || $data['activities'] === null) {
                $data['activities'] = '';
            }
            
            // Manejar certificado
            $certificateData = [];
            if ($request->hasFile('certificate')) {
                $certificateData = $this->uploadCertificate($request->file('certificate'), 'experience');
            }
            
            // Preparar datos para inserción
            $experienceData = [
                'user_id' => $user->id,
                'company_name' => $data['company_name'],
                'position' => $data['position'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'currently_working' => $data['currently_working'],
                'activities' => $data['activities'], // ← Campo requerido
                'sort_order' => 0,
                'is_featured' => $data['is_featured'] ?? false,
                'is_verified' => false,
            ];
            
            // Agregar campos opcionales solo si tienen valor
            $optionalFields = [
                'employment_type', 'supervisor_name', 'supervisor_phone', 'supervisor_email',
                'achievements', 'skills_used', 'technologies_used', 'industry_sector',
                'company_size', 'salary', 'currency', 'departure_reason', 'work_city',
                'work_country', 'is_remote', 'allow_contact'
            ];
            
            foreach ($optionalFields as $field) {
                if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '') {
                    $experienceData[$field] = $data[$field];
                }
            }
            
            // Agregar datos del certificado
            if (!empty($certificateData)) {
                $experienceData = array_merge($experienceData, $certificateData);
            }
            
            Log::info('Creating experience with data:', $experienceData);
            
            $experience = UserExperience::create($experienceData);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Experiencia agregada exitosamente',
                'experience' => $experience
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating experience: ' . $e->getMessage());
            Log::error('SQL Error: ' . $e->getPrevious()?->getMessage());
            Log::error('Data: ' . json_encode($data));
            return response()->json(['error' => 'Error al crear experiencia: ' . $e->getMessage()], 500);
        }
    }

    private function updateExperienceByIndex($data, $user, $request, $index)
    {
        try {
            // Buscar la experiencia por índice
            $experiences = $user->experiences()->ordered()->get();
            
            if (!isset($experiences[$index])) {
                return response()->json(['error' => 'Experiencia no encontrada'], 404);
            }
            
            $experience = $experiences[$index];
            
            // Actualizar campos
            $updateData = [];
            $allowedFields = [
                'company_name', 'position', 'employment_type', 'supervisor_name',
                'supervisor_phone', 'supervisor_email', 'start_date', 'end_date',
                'currently_working', 'activities', 'achievements', 'skills_used',
                'technologies_used', 'industry_sector', 'company_size', 'salary',
                'currency', 'departure_reason', 'work_city', 'work_country',
                'is_remote', 'is_featured', 'allow_contact'
            ];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    if ($data[$field] === 'undefined' || $data[$field] === 'null') {
                        $updateData[$field] = null;
                    } else {
                        $updateData[$field] = $data[$field];
                    }
                }
            }
            
            // Manejar currently_working
            if (isset($data['currently_working'])) {
                if ($data['currently_working'] == '1' || $data['currently_working'] === true) {
                    $updateData['currently_working'] = true;
                    $updateData['end_date'] = null;
                } else {
                    $updateData['currently_working'] = false;
                }
            }
            
            // Asegurar que activities tenga valor
            if (isset($updateData['activities']) && ($updateData['activities'] === null || $updateData['activities'] === '')) {
                $updateData['activities'] = '';
            }
            
            $experience->update($updateData);
            
            return response()->json([
                'success' => true,
                'message' => 'Experiencia actualizada exitosamente',
                'experience' => $experience->fresh()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating experience: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar experiencia: ' . $e->getMessage()], 500);
        }
    }

    private function deleteExperience($user, $index)
    {
        try {
            $experiences = $user->experiences()->ordered()->get();
            
            if (!isset($experiences[$index])) {
                return response()->json(['error' => 'Experiencia no encontrada'], 404);
            }
            
            $experience = $experiences[$index];
            
            // Eliminar certificado si existe
            if ($experience->certification_file_path) {
                Storage::disk('public')->delete($experience->certification_file_path);
            }
            
            $experience->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Experiencia eliminada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting experience: ' . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar experiencia: ' . $e->getMessage()], 500);
        }
    }

    private function uploadCertificate($file, $type)
    {
        $fileName = time() . '_' . $type . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('certificates/' . $type, $fileName, 'public');
        
        // Para experiencia usar certification_*
        if ($type === 'experience') {
            return [
                'certification_file_path' => $path,
                'certification_original_name' => $file->getClientOriginalName(),
                'certification_mime_type' => $file->getMimeType(),
                'certification_file_size' => $file->getSize()
            ];
        }
        
        // Para educación usar certificate_*
        return [
            'certificate_file_path' => $path,
            'certificate_original_name' => $file->getClientOriginalName(),
            'certificate_mime_type' => $file->getMimeType(),
            'certificate_file_size' => $file->getSize()
        ];
    }

    // Métodos para educación y otros...
    private function handleEducation(Request $request, $user, $action)
    {
        try {
            Log::info('=== HANDLING EDUCATION ===');
            
            // Los datos vienen directamente en el request
            $data = [
                'education_level' => $request->input('education_level'),
                'institution' => $request->input('institution'),
                'degree' => $request->input('degree'),
                'start_year' => $request->input('start_year'),
                'graduation_year' => $request->input('graduation_year'),
                'country' => $request->input('country', 'Colombia'),
                'department' => $request->input('department'),
                'city' => $request->input('city'),
                'is_primary' => $request->input('is_primary', false),
            ];
            
            Log::info('Education data processed:', [
                'action' => $action,
                'data' => $data
            ]);
            
            // Validar datos mínimos
            if (empty($data['education_level']) || empty($data['institution']) || empty($data['degree'])) {
                return response()->json([
                    'error' => 'Faltan datos obligatorios: nivel educativo, institución y título son requeridos',
                    'received_data' => $data
                ], 422);
            }
            
            // Manejar según la acción
            switch ($action) {
                case 'add':
                    return $this->createEducation($data, $user, $request);
                
                case 'edit':
                    $index = $request->input('index');
                    return $this->updateEducationByIndex($data, $user, $request, $index);
                
                case 'delete':
                    $index = $request->input('index');
                    return $this->deleteEducation($user, $index);
                
                default:
                    return $this->createEducation($data, $user, $request);
            }
            
        } catch (\Exception $e) {
            Log::error('Error handling education: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar educación: ' . $e->getMessage()], 500);
        }
    }

    private function createEducation($data, $user, $request)
    {
        DB::beginTransaction();
        
        try {
            Log::info('=== CREATING EDUCATION ===');
            
            // Filtrar valores undefined o null problemáticos
            foreach ($data as $key => $value) {
                if ($value === 'undefined' || $value === 'null') {
                    $data[$key] = null;
                }
            }
            
            // Si es primary, quitar primary de otros
            if ($data['is_primary'] == '1' || $data['is_primary'] === true) {
                UserEducation::where('user_id', $user->id)
                    ->update(['is_primary' => false]);
                $data['is_primary'] = true;
            } else {
                $data['is_primary'] = false;
            }
            
            // Convertir años a enteros
            if (isset($data['start_year']) && !empty($data['start_year'])) {
                $data['start_year'] = (int) $data['start_year'];
            }
            
            if (isset($data['graduation_year']) && !empty($data['graduation_year'])) {
                $data['graduation_year'] = (int) $data['graduation_year'];
            }
            
            // Manejar certificado
            $certificateData = [];
            if ($request->hasFile('certificate')) {
                $certificateData = $this->uploadCertificate($request->file('certificate'), 'education');
            }
            
            // Preparar datos para inserción
            $educationData = [
                'user_id' => $user->id,
                'education_level' => $data['education_level'],
                'institution' => $data['institution'],
                'degree' => $data['degree'],
                'start_year' => $data['start_year'],
                'graduation_year' => $data['graduation_year'],
                'country' => $data['country'],
                'department' => $data['department'],
                'city' => $data['city'],
                'sort_order' => 0,
                'is_primary' => $data['is_primary'],
                'is_verified' => false,
            ];
            
            // Agregar datos del certificado
            if (!empty($certificateData)) {
                $educationData = array_merge($educationData, $certificateData);
            }
            
            Log::info('Creating education with data:', $educationData);
            
            $education = UserEducation::create($educationData);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Educación agregada exitosamente',
                'education' => $education
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating education: ' . $e->getMessage());
            Log::error('SQL Error: ' . $e->getPrevious()?->getMessage());
            Log::error('Data: ' . json_encode($data));
            return response()->json(['error' => 'Error al crear educación: ' . $e->getMessage()], 500);
        }
    }

    private function updateEducationByIndex($data, $user, $request, $index)
    {
        try {
            // Buscar la educación por índice
            $educations = $user->educations()->ordered()->get();
            
            if (!isset($educations[$index])) {
                return response()->json(['error' => 'Educación no encontrada'], 404);
            }
            
            $education = $educations[$index];
            
            // Si va a ser primary, quitar primary de otros
            if (isset($data['is_primary']) && ($data['is_primary'] == '1' || $data['is_primary'] === true)) {
                UserEducation::where('user_id', $user->id)
                    ->where('id', '!=', $education->id)
                    ->update(['is_primary' => false]);
            }
            
            // Actualizar campos
            $updateData = [];
            $allowedFields = [
                'education_level', 'institution', 'degree', 'start_year', 'graduation_year',
                'country', 'department', 'city', 'is_primary'
            ];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    if ($data[$field] === 'undefined' || $data[$field] === 'null') {
                        $updateData[$field] = null;
                    } else {
                        $updateData[$field] = $data[$field];
                    }
                }
            }
            
            // Convertir años a enteros
            if (isset($updateData['start_year']) && !empty($updateData['start_year'])) {
                $updateData['start_year'] = (int) $updateData['start_year'];
            }
            
            if (isset($updateData['graduation_year']) && !empty($updateData['graduation_year'])) {
                $updateData['graduation_year'] = (int) $updateData['graduation_year'];
            }
            
            $education->update($updateData);
            
            return response()->json([
                'success' => true,
                'message' => 'Educación actualizada exitosamente',
                'education' => $education->fresh()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating education: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar educación: ' . $e->getMessage()], 500);
        }
    }

    private function deleteEducation($user, $index)
    {
        try {
            $educations = $user->educations()->ordered()->get();
            
            if (!isset($educations[$index])) {
                return response()->json(['error' => 'Educación no encontrada'], 404);
            }
            
            $education = $educations[$index];
            
            // Eliminar certificado si existe
            if ($education->certificate_file_path) {
                Storage::disk('public')->delete($education->certificate_file_path);
            }
            
            $education->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Educación eliminada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting education: ' . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar educación: ' . $e->getMessage()], 500);
        }
    }

    private function updateBasicInfo(Request $request, $user)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'idNumber' => 'nullable|string|max:20',
                'phone' => 'nullable|string|max:20',
                'birthDate' => 'nullable|date',
                'gender' => 'nullable|in:Masculino,Femenino,Otro',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
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
            $user->idNumber = $request->input('idNumber');
            $user->phone = $request->input('phone');
            $user->birthDate = $request->input('birthDate');
            $user->gender = $request->input('gender');
            $user->address = $request->input('address');
            $user->city = $request->input('city');
            $user->postal_code = $request->input('postal_code');

            // Manejar foto de perfil
            if ($request->hasFile('photo')) {
                try {
                    // Eliminar foto anterior si existe
                    if ($user->photo) {
                        ImageHelper::deletePhoto($user->photo);
                    }

                    // Procesar nueva foto (con o sin redimensionamiento)
                    if (extension_loaded('gd')) {
                        $photoPath = ImageHelper::processAndResizeProfilePhoto($request->file('photo'), $user->id);
                    } else {
                        $photoPath = ImageHelper::processProfilePhoto($request->file('photo'), $user->id);
                    }
                    
                    $user->photo = $photoPath;
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al procesar la imagen: ' . $e->getMessage()
                    ]);
                }
            }

            $user->save();

            // Devolver datos actualizados
            $userData = $user->fresh()->toArray();
            
            return response()->json([
                'success' => true,
                'data' => $userData,
                'message' => 'Información personal actualizada correctamente'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating basic info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la información: ' . $e->getMessage()
            ], 500);
        }
    }

    // Métodos para endpoints separados
    public function updateExperience(Request $request)
    {
        return $this->update($request);
    }

    public function updateEducation(Request $request)
    {
        return $this->update($request);
    }
}