<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTechnician
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user || $user->user_type !== 'technician') {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Solo los técnicos pueden acceder a esta funcionalidad.'
            ], 403);
        }
        
        return $next($request);
    }
}