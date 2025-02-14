<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$role): Response
    { 

        $user = $request->user();

        // Check if user has the required roles
        if ($user && $user->hasRole($role)) {
            return $next($request);
        }

        return response()->json(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
    }
}
