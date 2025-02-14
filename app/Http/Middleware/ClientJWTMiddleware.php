<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;

class ClientJWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get the token from the request headers
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            // Attempt to authenticate the token
            // Get the client ID from the token
            $clientId = JWTAuth::setToken($token)->getPayload()->get('sub');

            // Find the client by ID
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json(['error' => 'Client not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token is invalid: ' . $e->getMessage()], 401);
        }

        // Set the authenticated client in the request
        $request->attributes->set('client', $client);

        return $next($request);
    }
}
