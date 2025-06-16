<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'service_request_id' => 'required|exists:service_requests,id',
                'technician_id' => 'required|exists:users,id',
                'overall_rating' => 'required|integer|min:1|max:5',
                'quality_rating' => 'nullable|integer|min:1|max:5',
                'punctuality_rating' => 'nullable|integer|min:1|max:5',
                'communication_rating' => 'nullable|integer|min:1|max:5',
                'price_rating' => 'nullable|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'pros' => 'nullable|string|max:500',
                'cons' => 'nullable|string|max:500',
                'is_recommended' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar que el usuario es el cliente de la solicitud
            $serviceRequest = ServiceRequest::where('id', $request->service_request_id)
                ->where('client_id', $request->user()->id)
                ->where('status', 'completed')
                ->first();

            if (!$serviceRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para reseñar esta solicitud o no está completada'
                ], 403);
            }

            // Verificar que no haya reseña previa
            $existingReview = Review::where('service_request_id', $request->service_request_id)
                ->where('client_id', $request->user()->id)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya has dejado una reseña para esta solicitud'
                ], 422);
            }

            $review = Review::create([
                'client_id' => $request->user()->id,
                'technician_id' => $request->technician_id,
                'service_request_id' => $request->service_request_id,
                'overall_rating' => $request->overall_rating,
                'quality_rating' => $request->quality_rating ?? $request->overall_rating,
                'punctuality_rating' => $request->punctuality_rating ?? $request->overall_rating,
                'communication_rating' => $request->communication_rating ?? $request->overall_rating,
                'price_rating' => $request->price_rating ?? $request->overall_rating,
                'comment' => $request->comment,
                'pros' => $request->pros,
                'cons' => $request->cons,
                'is_recommended' => $request->is_recommended ?? true,
                'is_public' => true,
                'is_verified' => true
            ]);

            // Actualizar rating del técnico
            $this->updateTechnicianRating($request->technician_id);

            return response()->json([
                'success' => true,
                'data' => $review,
                'message' => 'Reseña creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear reseña: ' . $e->getMessage()
            ], 500);
        }
    }

    public function indexByTechnician(Request $request, $technicianId): JsonResponse
    {
        try {
            $reviews = Review::where('technician_id', $technicianId)
                ->where('is_public', true)
                ->with(['client', 'serviceRequest'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $reviews
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener reseñas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function indexByUser()
    {
        try {
            $reviews = Review::where('client_id', Auth::id())
                ->with(['technician', 'serviceRequest'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $reviews
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tus reseñas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $review = Review::where('id', $id)
                ->where('client_id', Auth::id())
                ->first();

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reseña no encontrada'
                ], 404);
            }

            $technicianId = $review->technician_id;
            $review->delete();

            // Actualizar promedio del técnico
            $this->updateTechnicianRating($technicianId);

            return response()->json([
                'success' => true,
                'message' => 'Reseña eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar reseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function updateTechnicianRating($technicianId)
    {
        $averageRating = Review::where('technician_id', $technicianId)
            ->avg('overall_rating');
        
        $totalReviews = Review::where('technician_id', $technicianId)->count();

        // Actualizar el perfil del técnico
        $techProfile = \App\Models\TechnicianProfile::where('user_id', $technicianId)->first();
        if ($techProfile) {
            $techProfile->update([
                'average_rating' => round($averageRating, 2),
                'total_reviews' => $totalReviews
            ]);
        }
    }
}