<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $userType): Response
    {
        if ($request->user()->user_type !== $userType) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No tienes permisos para acceder a este recurso'
                ], 403);
            }

            return redirect()->route('home')->with('error', 'No tienes permisos para acceder a esta página');
        }

        return $next($request);
    }
}
