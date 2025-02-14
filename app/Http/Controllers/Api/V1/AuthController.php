<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {

        // validate the request
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422); // 422 Unprocessable Entity
        }

        try {
            // Check if the 'guest' role exists
            $guestRole = Role::where('name', 'guest')->first();
            if (!$guestRole) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Role "guest" does not exist',
                ], 404);
            }

            // creating the user
            $user = User::create([
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            // Assign the 'guest' role to the new user
            $user->assignRole($guestRole->id);

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully.',
                'data' => new UserResource($user)
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'User registration failed ' . $th->getMessage(),
            ], 400);
        }
    }

    /**
     * Log in user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' =>  $validator->errors(),
            ], 422); // 422 Unprocessable Entity
        }

        $credentials = $request->only('email', 'password');

        try {
            // Attempt to authenticate and get a JWT token
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            return $this->respondWithToken($token);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create token ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh the JWT token.
     *
     * @return \Illuminate\Http\Response
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return $this->respondWithToken($newToken);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh token ' .  $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Return the token structure.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60, // (* 60) to get TTL in seconds
        ], 200);
    }
    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,

                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'permissions' => $role->permissions->map(function ($permission) {
                                return [
                                    'id' => $permission->id,
                                    'name' => $permission->name,
                                ];
                            })->toArray(),
                            'users' => $role->users->map(function ($user) {
                                return [
                                    'id' => $user->id,
                                    'username' => $user->username,
                                ];
                            })
                        ];
                    }),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }



    /**
     * Log out user.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to log out ' . $e->getMessage(),
            ], 500);
        }
    }
}
