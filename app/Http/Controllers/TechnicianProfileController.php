<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TechnicianProfile;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class TechnicianProfileController extends Controller
{
    /**
     * 🔍 FUNCIÓN: Listar técnicos disponibles (PÚBLICA)
     * USO: Clientes buscan técnicos disponibles
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::where('user_type', 'technician')
                ->where('is_active', true)
                ->with(['technicianProfile', 'reviews'])
                ->whereHas('technicianProfile', function($q) {
                    $q->where('is_available', true);
                });

            // 🔍 Filtros de búsqueda
            if ($request->has('specialty')) {
                $query->whereHas('technicianProfile', function($q) use ($request) {
                    $q->where('specialty', 'like', '%' . $request->specialty . '%');
                });
            }

            if ($request->has('city')) {
                $query->where('city', 'like', '%' . $request->city . '%');
            }

            if ($request->has('min_rating') && $request->min_rating > 0) {
                $query->whereHas('technicianProfile', function($q) use ($request) {
                    $q->where('rating_average', '>=', $request->min_rating);
                });
            }

            if ($request->has('max_hourly_rate') && $request->max_hourly_rate > 0) {
                $query->whereHas('technicianProfile', function($q) use ($request) {
                    $q->where('hourly_rate', '<=', $request->max_hourly_rate);
                });
            }

            if ($request->has('verified_only') && $request->verified_only) {
                $query->whereHas('technicianProfile', function($q) {
                    $q->where('is_verified', true);
                });
            }

            // 📊 Ordenamiento
            $sortBy = $request->get('sort_by', 'rating');
            switch ($sortBy) {
                case 'rating':
                    $query->leftJoin('technician_profiles', 'users.id', '=', 'technician_profiles.user_id')
                          ->orderBy('technician_profiles.rating_average', 'desc');
                    break;
                case 'experience':
                    $query->leftJoin('technician_profiles', 'users.id', '=', 'technician_profiles.user_id')
                          ->orderBy('technician_profiles.experience_years', 'desc');
                    break;
                case 'price_low':
                    $query->leftJoin('technician_profiles', 'users.id', '=', 'technician_profiles.user_id')
                          ->orderBy('technician_profiles.hourly_rate', 'asc');
                    break;
                case 'price_high':
                    $query->leftJoin('technician_profiles', 'users.id', '=', 'technician_profiles.user_id')
                          ->orderBy('technician_profiles.hourly_rate', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            $technicians = $query->select('users.*')->paginate(12);

            return response()->json([
                'success' => true,
                'data' => $technicians,
                'filters_applied' => $request->only(['specialty', 'city', 'min_rating', 'max_hourly_rate', 'verified_only', 'sort_by'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al listar técnicos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener técnicos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 👤 FUNCIÓN: Mostrar perfil público de técnico (PÚBLICA)
     * USO: Clientes ven detalles completos de un técnico
     */
    public function show($id): JsonResponse
    {
        try {
            $technician = User::where('user_type', 'technician')
                ->where('id', $id)
                ->with([
                    'technicianProfile',
                    'reviews' => function($q) {
                        $q->where('is_public', true)
                          ->with('client:id,name')
                          ->orderBy('created_at', 'desc')
                          ->limit(10);
                    },
                    'assignedServiceRequests' => function($q) {
                        $q->where('status', 'completed')
                          ->with('client:id,name')
                          ->orderBy('completed_at', 'desc')
                          ->limit(5);
                    }
                ])
                ->first();

            if (!$technician) {
                return response()->json([
                    'success' => false,
                    'message' => 'Técnico no encontrado'
                ], 404);
            }

            // 📊 Calcular estadísticas del técnico
            $stats = $this->calculateTechnicianStats($technician);

            // 📈 Datos de calificaciones por categoría
            $ratingBreakdown = $this->getRatingBreakdown($technician);

            return response()->json([
                'success' => true,
                'data' => [
                    'technician' => $technician,
                    'stats' => $stats,
                    'rating_breakdown' => $ratingBreakdown,
                    'recent_reviews' => $technician->reviews,
                    'recent_jobs' => $technician->assignedServiceRequests
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener perfil del técnico: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener perfil del técnico',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✏️ FUNCIÓN: Actualizar configuración específica del técnico (PROTEGIDA)
     * USO: Técnicos actualizan disponibilidad, tarifas, servicios específicos
     */
    public function updateTechnicianSettings(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user->user_type !== 'technician') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los técnicos pueden actualizar esta configuración'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'is_available' => 'sometimes|boolean',
                'hourly_rate' => 'sometimes|numeric|min:0|max:1000000',
                'service_areas' => 'sometimes|array',
                'service_areas.*' => 'string|max:100',
                'availability_schedule' => 'sometimes|array',
                'emergency_service' => 'sometimes|boolean',
                'travel_radius' => 'sometimes|integer|min:1|max:100',
                'minimum_service_fee' => 'sometimes|numeric|min:0',
                'accepts_remote_work' => 'sometimes|boolean',
                'preferred_contact_method' => 'sometimes|in:phone,email,whatsapp',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $techProfile = $user->technicianProfile;
            
            if (!$techProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil técnico no encontrado'
                ], 404);
            }

            // ⚙️ Actualizar configuraciones específicas
            $settingsFields = [
                'is_available', 'hourly_rate', 'service_areas', 
                'availability_schedule', 'emergency_service', 
                'travel_radius', 'minimum_service_fee', 
                'accepts_remote_work', 'preferred_contact_method'
            ];

            foreach ($settingsFields as $field) {
                if ($request->has($field)) {
                    $techProfile->$field = $request->$field;
                }
            }

            $techProfile->save();

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada exitosamente',
                'data' => $techProfile
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar configuración técnica: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔍 FUNCIÓN: Búsqueda avanzada de técnicos (PÚBLICA)
     * USO: Clientes buscan técnicos con criterios específicos
     */
    public function search(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'query' => 'nullable|string|max:255',
                'specialty' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'min_rating' => 'nullable|numeric|min:1|max:5',
                'max_hourly_rate' => 'nullable|numeric|min:0',
                'min_experience' => 'nullable|integer|min:0',
                'skills' => 'nullable|array',
                'skills.*' => 'string|max:100',
                'emergency_available' => 'nullable|boolean',
                'remote_work' => 'nullable|boolean',
                'verified_only' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros de búsqueda inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = User::where('user_type', 'technician')
                ->where('is_active', true)
                ->with(['technicianProfile', 'reviews'])
                ->whereHas('technicianProfile', function($q) {
                    $q->where('is_available', true);
                });

            // 🔍 Búsqueda por texto libre
            if ($request->query) {
                $searchTerm = $request->query;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhereHas('technicianProfile', function($tq) use ($searchTerm) {
                          $tq->where('specialty', 'like', "%{$searchTerm}%")
                             ->orWhere('bio', 'like', "%{$searchTerm}%");
                      });
                });
            }

            // 🎯 Aplicar filtros específicos
            $this->applySearchFilters($query, $request);

            // 📊 Ordenamiento inteligente
            $this->applySorting($query, $request);

            $results = $query->select('users.*')->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $results,
                'search_params' => $request->all(),
                'total_found' => $results->total()
            ]);

        } catch (\Exception $e) {
            Log::error('Error en búsqueda de técnicos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 FUNCIÓN: Estadísticas detalladas del técnico (PÚBLICA)
     * USO: Mostrar métricas de rendimiento del técnico
     */
    public function stats($id)
    {
        try {
            $technician = User::where('user_type', 'technician')
                ->where('id', $id)
                ->with(['technicianProfile', 'assignedServiceRequests', 'reviews'])
                ->first();

            if (!$technician) {
                return response()->json([
                    'success' => false,
                    'message' => 'Técnico no encontrado'
                ], 404);
            }

            $stats = $this->calculateDetailedStats($technician);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📈 FUNCIÓN: Dashboard del técnico (PROTEGIDA)
     * USO: Técnicos ven su dashboard personal
     */
    public function dashboard()
    {
        try {
            $user = Auth::user();

            if ($user->user_type !== 'technician') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso solo para técnicos'
                ], 403);
            }

            $dashboardData = [
                'profile_completeness' => $user->technicianProfile->profile_completeness ?? 0,
                'pending_requests' => $this->getPendingRequests($user),
                'active_jobs' => $this->getActiveJobs($user),
                'recent_reviews' => $this->getRecentReviews($user),
                'earnings_summary' => $this->getEarningsSummary($user),
                'performance_metrics' => $this->getPerformanceMetrics($user),
            ];

            return response()->json([
                'success' => true,
                'data' => $dashboardData
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 🛠️ MÉTODOS AUXILIARES

    private function calculateTechnicianStats($technician)
    {
        $totalJobs = $technician->assignedServiceRequests->count();
        $completedJobs = $technician->assignedServiceRequests->where('status', 'completed')->count();
        $totalReviews = $technician->reviews->count();
        $avgRating = $technician->reviews->avg('overall_rating') ?? 0;

        return [
            'total_jobs' => $totalJobs,
            'completed_jobs' => $completedJobs,
            'completion_rate' => $totalJobs > 0 ? round(($completedJobs / $totalJobs) * 100, 1) : 0,
            'total_reviews' => $totalReviews,
            'average_rating' => round($avgRating, 2),
            'response_rate' => 95, // Calcular basado en tiempos de respuesta
            'on_time_rate' => 90,  // Calcular basado en horarios cumplidos
            'profile_completeness' => $technician->technicianProfile->profile_completeness ?? 0,
            'is_verified' => $technician->technicianProfile->is_verified ?? false,
            'member_since' => $technician->created_at->format('Y-m-d'),
        ];
    }

    private function getRatingBreakdown($technician)
    {
        return [
            'quality_avg' => round($technician->reviews->avg('quality_rating') ?? 0, 2),
            'punctuality_avg' => round($technician->reviews->avg('punctuality_rating') ?? 0, 2),
            'communication_avg' => round($technician->reviews->avg('communication_rating') ?? 0, 2),
            'price_avg' => round($technician->reviews->avg('price_rating') ?? 0, 2),
        ];
    }

    private function applySearchFilters($query, $request)
    {
        // Implementar filtros específicos
        if ($request->specialty) {
            $query->whereHas('technicianProfile', function($q) use ($request) {
                $q->where('specialty', 'like', '%' . $request->specialty . '%');
            });
        }

        if ($request->city) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Más filtros...
    }

    private function applySorting($query, $request)
    {
        $sortBy = $request->get('sort_by', 'rating');
        
        switch ($sortBy) {
            case 'rating':
                $query->leftJoin('technician_profiles', 'users.id', '=', 'technician_profiles.user_id')
                      ->orderBy('technician_profiles.rating_average', 'desc');
                break;
            // Más opciones de ordenamiento...
        }
    }

    private function calculateDetailedStats($technician)
    {
        // Implementar cálculos detallados de estadísticas
        return [
            'basic_stats' => $this->calculateTechnicianStats($technician),
            'monthly_performance' => [], // Datos mensuales
            'service_breakdown' => [],   // Por tipo de servicio
            'client_satisfaction' => [], // Métricas de satisfacción
        ];
    }

    private function getPendingRequests($user)
    {
        return ServiceRequest::where('technician_id', $user->id)
            ->whereIn('status', ['assigned', 'quoted'])
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function getActiveJobs($user)
    {
        return ServiceRequest::where('technician_id', $user->id)
            ->where('status', 'in_progress')
            ->with('client')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    private function getRecentReviews($user)
    {
        return $user->reviews()
            ->with('client:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function getEarningsSummary($user)
    {
        $thisMonth = ServiceRequest::where('technician_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [now()->startOfMonth(), now()])
            ->sum('final_cost');

        return [
            'this_month' => $thisMonth,
            'last_month' => 0, // Calcular mes anterior
            'total_earnings' => 0, // Calcular total histórico
        ];
    }

    private function getPerformanceMetrics($user)
    {
        return [
            'avg_response_time' => '2 horas', // Calcular tiempo promedio de respuesta
            'completion_rate' => 95,          // Calcular tasa de finalización
            'repeat_clients' => 0,           // Calcular clientes recurrentes
        ];
    }
}
