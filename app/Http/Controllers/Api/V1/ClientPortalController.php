<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ClientResource;
use App\Http\Resources\V1\PaymentResource;
use App\Http\Resources\V1\ScheduleResource;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Schedule;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class ClientPortalController extends Controller
{

    public function login(Request $request)
    {
        // Validate request
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Manually retrieve the client
        $client = Client::where('email', $request->email)->first();

        // Check if client exists and the password is correct
        if (!$client || ! Hash::check($request->password, $client->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Generate JWT token
        $token = JWTAuth::fromUser($client);

        return response()->json(compact('token'));
    }


    public function getProfile(Request $request)
    {
        // Retrieve the token from the request
        $token = $request->bearerToken();

        try {
            // Get the client ID from the token
            $clientId = JWTAuth::setToken($token)->getPayload()->get('sub');

            // Find the client by ID
            $client = Client::find($clientId);

            return response()->json([
                'status' => 'success',
                'data' => new ClientResource($client)
            ], 200); // 200 ok
        }
        // problem encountred while getting clients
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'errors' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }


    public function editProfile(Request $request)
    {
        // Retrieve the token from the request
        $token = $request->bearerToken();

        try {
            // Get the client ID from the token
            $clientId = JWTAuth::setToken($token)->getPayload()->get('sub');

            // Find the client by ID
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client not found'
                ], 404); // 404 Not Found
            }

            // Validate the incoming request
            $validatedData = $request->validate([
                'firstname' => 'nullable|string|max:255',
                'lastname' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:clients,email,' . $client->id,
                'phone' => 'nullable|string|max:15',
                'password' => 'nullable|string|min:8',
            ]);

            if ($request->input('password')) {
                $password = $request->input('password');
                $hashedPassword = bcrypt($password);
            } else
                $hashedPassword = $client->password;

            // update the client
            $client->update($request->only(['firstname', 'lastname', 'email', 'phone']) + ['password' => $hashedPassword]);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => new ClientResource($client)
            ], 200); // 200 OK
        }
        // Error handling
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'errors' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }



    public function schedule(Request $request)
    {        // Retrieve the token from the request
        $token = $request->bearerToken();

        try {
            // Get the client ID from the token
            $clientId = JWTAuth::setToken($token)->getPayload()->get('sub');

            // Find the client by ID
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client not found'
                ], 404); // 404 Not Found
            }

            $schedules = Schedule::where('client_id', $client->id)->get();


            return response()->json([
                'status' => 'success',
                'data' => ScheduleResource::collection($schedules)
            ], 200); // 200 ok
        }
        // problem encountred while getting clients
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'errors' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    public function payments(Request $request)
    {
        // Retrieve the token from the request
        $token = $request->bearerToken();

        try {
            // Get the client ID from the token
            $clientId = JWTAuth::setToken($token)->getPayload()->get('sub');

            // Find the client by ID
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client not found'
                ], 404); // 404 Not Found
            }
            $payments = Payment::where('client_id', $client->id)->get();

            return response()->json([
                'status' => 'success',
                'data' => PaymentResource::collection($payments)
            ], 200); // 200 ok
        }
        // problem encountred while getting clients
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'errors' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }


    // public function feedback(Request $request)
    // {

    //     try {

    //         return response()->json([
    //             'status' => 'success',
    //             'data' => 'feedback'
    //         ], 200); // 200 ok
    //     }
    //     // problem encountred while getting clients
    //     catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred',
    //             'errors' => $th->getMessage()
    //         ], 500); // 500 Internal Server Error
    //     }
    // }

    public function logout(Request $request)
    {
        // Retrieve the token from the request
        $token = $request->bearerToken();
    
        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 400); // 400 Bad Request
        }
    
        try {
            // Invalidate the token
            JWTAuth::invalidate($token);
    
            return response()->json(['message' => 'Successfully logged out'], 200); // 200 OK
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to logout, please try again'], 500); // 500 Internal Server Error
        }
    }
    
}
