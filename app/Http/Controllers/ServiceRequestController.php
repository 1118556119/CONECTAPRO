<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestPhoto;

class ServiceRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Obtener solicitudes reales del usuario
            $requests = ServiceRequest::where('client_id', $user->id)
                ->with(['technician', 'photos'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $requests,
                'message' => 'Solicitudes obtenidas correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener solicitudes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'urgency' => 'required|in:baja,media,alta,critica',
                'equipment_type' => 'required|string|max:255',
                'brand' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'city' => 'required|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'preferred_date' => 'nullable|date|after:today',
                'preferred_time' => 'nullable|string|max:20',
                'comments' => 'nullable|string|max:1000',
                'other_type' => 'nullable|string|max:255',
                'service_type_detail' => 'nullable|string|max:255',
                'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $validatedData = $validator->validated();

            // Crear la solicitud de servicio con verificación de campos
            $serviceRequest = ServiceRequest::create([
                'client_id' => $user->id,
                'service_type' => $validatedData['type'],
                'urgency_level' => $validatedData['urgency'],
                'description' => $validatedData['description'],
                'equipment_type' => $validatedData['equipment_type'],
                'equipment_brand' => $validatedData['brand'],
                'service_address' => $validatedData['address'],
                'service_city' => $validatedData['city'],
                'service_postal_code' => $validatedData['postal_code'] ?? null,
                'preferred_date' => $validatedData['preferred_date'] ?? null,
                'preferred_time' => $validatedData['preferred_time'] ?? null,
                'client_notes' => $validatedData['comments'] ?? null,
                'service_details' => $validatedData['service_type_detail'] ?? null,
                'status' => 'pending'
            ]);

            // Manejar subida de archivos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $fileName = time() . '_' . $index . '.' . $photo->getClientOriginalExtension();
                    $filePath = $photo->storeAs('service_requests/' . $serviceRequest->id, $fileName, 'public');
                    
                    ServiceRequestPhoto::create([
                        'service_request_id' => $serviceRequest->id,
                        'file_name' => $fileName,
                        'original_name' => $photo->getClientOriginalName(),
                        'file_path' => $filePath,
                        'mime_type' => $photo->getMimeType(),
                        'file_size' => $photo->getSize(),
                        'photo_type' => 'problem',
                        'uploaded_by' => $user->id,
                        'is_public' => true
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $serviceRequest->load(['photos']),
                'message' => 'Solicitud de servicio creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error al crear solicitud: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $serviceRequest = ServiceRequest::with(['client', 'technician', 'photos'])
                ->findOrFail($id);

            // Verificar que el usuario tiene permiso para ver esta solicitud
            if ($serviceRequest->client_id !== $request->user()->id && 
                $serviceRequest->technician_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver esta solicitud'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $serviceRequest,
                'message' => 'Solicitud obtenida correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function acceptQuote(Request $request, $id): JsonResponse
    {
        try {
            $serviceRequest = ServiceRequest::where('id', $id)
                ->where('client_id', $request->user()->id)
                ->where('status', 'quoted')
                ->firstOrFail();

            $serviceRequest->update([
                'status' => 'accepted',
                'accepted_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cotización aceptada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aceptar cotización: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rejectQuote(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:255',
                'rejection_comments' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $serviceRequest = ServiceRequest::where('id', $id)
                ->where('client_id', $request->user()->id)
                ->where('status', 'quoted')
                ->firstOrFail();

            $serviceRequest->update([
                'status' => 'pending',
                'technician_id' => null,
                'quoted_price' => null,
                'technician_notes' => null,
                'rejection_reason' => $request->rejection_reason,
                'rejection_comments' => $request->rejection_comments,
                'rejected_at' => now(),
                'quoted_at' => null,
                'assigned_at' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cotización rechazada. La solicitud está nuevamente disponible.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar cotización: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método para técnicos - ver solicitudes disponibles
    public function availableRequests(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Validar que el usuario sea técnico
            if ($user->user_type !== 'technician') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. Solo los técnicos pueden ver solicitudes disponibles.'
                ], 403);
            }
            
            $requests = ServiceRequest::where('status', 'pending')
                ->with(['client', 'photos'])
                ->orderBy('urgency_level', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $requests,
                'message' => 'Solicitudes disponibles obtenidas correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener solicitudes: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método para técnicos - postularse a una solicitud
    public function applyToRequest(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Validar que el usuario sea técnico
            if ($user->user_type !== 'technician') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. Solo los técnicos pueden postularse a solicitudes.'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'quoted_price' => 'required|numeric|min:0',
                'technician_notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $serviceRequest = ServiceRequest::where('id', $id)
                ->where('status', 'pending')
                ->firstOrFail();

            // Validar que el técnico no se postule a su propia solicitud (por si acaso)
            if ($serviceRequest->client_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes postularte a tu propia solicitud.'
                ], 403);
            }

            $serviceRequest->update([
                'technician_id' => $user->id,
                'quoted_price' => $request->quoted_price,
                'technician_notes' => $request->technician_notes,
                'status' => 'quoted',
                'quoted_at' => now(),
                'assigned_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Te has postulado exitosamente a esta solicitud'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al postularse: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelRequest(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'cancellation_reason' => 'required|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $serviceRequest = ServiceRequest::where('id', $id)
                ->where('client_id', $request->user()->id)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->firstOrFail();

            $serviceRequest->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud cancelada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function startWork(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Validar que el usuario sea técnico
            if ($user->user_type !== 'technician') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. Solo los técnicos pueden iniciar trabajos.'
                ], 403);
            }
            
            $serviceRequest = ServiceRequest::where('id', $id)
                ->where('technician_id', $user->id)
                ->where('status', 'accepted')
                ->firstOrFail();

            $serviceRequest->update([
                'status' => 'in_progress',
                'started_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Trabajo iniciado correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar trabajo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function completeWork(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Validar que el usuario sea técnico
            if ($user->user_type !== 'technician') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. Solo los técnicos pueden completar trabajos.'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'final_cost' => 'nullable|numeric|min:0',
                'completion_notes' => 'nullable|string|max:1000',
                'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $serviceRequest = ServiceRequest::where('id', $id)
                ->where('technician_id', $user->id)
                ->where('status', 'in_progress')
                ->firstOrFail();

            $serviceRequest->update([
                'status' => 'completed',
                'final_cost' => $request->final_cost ?? $serviceRequest->quoted_price,
                'technician_notes' => $serviceRequest->technician_notes . "\n\nCompletion Notes: " . ($request->completion_notes ?? ''),
                'completed_at' => now()
            ]);

            // Manejar fotos de finalización
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $fileName = 'completion_' . time() . '_' . $index . '.' . $photo->getClientOriginalExtension();
                    $filePath = $photo->storeAs('service_requests/' . $serviceRequest->id, $fileName, 'public');
                    
                    ServiceRequestPhoto::create([
                        'service_request_id' => $serviceRequest->id,
                        'file_name' => $fileName,
                        'original_name' => $photo->getClientOriginalName(),
                        'file_path' => $filePath,
                        'mime_type' => $photo->getMimeType(),
                        'file_size' => $photo->getSize(),
                        'photo_type' => 'after',
                        'uploaded_by' => $user->id,
                        'is_public' => true
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Trabajo completado correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al completar trabajo: ' . $e->getMessage()
            ], 500);
        }
    }

    // NUEVO MÉTODO: Para que los técnicos vean sus trabajos asignados
    public function myAssignedRequests(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Validar que el usuario sea técnico
            if ($user->user_type !== 'technician') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. Solo los técnicos pueden ver sus trabajos asignados.'
                ], 403);
            }
            
            // Obtener solicitudes asignadas al técnico (estados después de "pending")
            $requests = ServiceRequest::where('technician_id', $user->id)
                ->whereIn('status', ['quoted', 'accepted', 'in_progress', 'completed'])
                ->with(['client', 'photos'])
                ->orderBy('status')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $requests,
                'message' => 'Trabajos asignados obtenidos correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener trabajos asignados: ' . $e->getMessage()
            ], 500);
        }
    }
}