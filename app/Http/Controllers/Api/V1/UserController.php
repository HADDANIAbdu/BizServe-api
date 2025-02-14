<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        try {

            // check if user has permission to diplay all users using UserPolicy
            $this->authorize('viewAny', User::class);

            // Load users with roles
            $users = User::paginate(6);

            return response()->json([
                'status' => 'success',
                'data' => UserResource::collection($users),
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'next_page_url' => $users->nextPageUrl(),
                    'prev_page_url' => $users->previousPageUrl(),
                ]
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage(),
            ], 403); // 403 Forbidden
        }
        // problem encountred while getting users
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred :' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        try {
            // check if user has permission to create a new user using UserPolicy
            $this->authorize('create', User::class);

            // validate the request
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'roles' => 'nullable|array',
                'roles.*' => 'exists:roles,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422); // 422 Unprocessable Entity
            }

            // creating the user
            $user = User::create([
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            // attaching the roles to user
            $rolesIds = $request->input('roles');
            // check if roles are not null (empty)
            if ($rolesIds) {

                // converting the roleNames to array if only one role send
                $rolesIds = is_array($rolesIds) ? $rolesIds : [$rolesIds];

                // Sync the permissions with the role
                $user->roles()->sync($rolesIds);
            } else {
                // check if role 'guest' exists in database
                $guestRole = Role::where('name', 'guest')->firstOrFail();
                if (!$guestRole) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Role guest does not exist',
                        'errors' => 'Role guest does not exist'
                    ], 404);
                }
                // assigning guest role
                $user->assignRole($guestRole->id);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => new UserResource($user)
            ], 201); // 201 Created

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage(),
            ], 403); // 403 Forbidden
        }
        // problem encountred while creating user
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred :' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        try {
            // check if user has permission to diplay a specific user using UserPolicy
            $this->authorize('view', User::class);

            return response()->json([
                'status' => 'success',
                'message' => 'User details retrieved successfully',
                'data' => new UserResource($user)
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage(),
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred :' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            // check if user has permission to update using UserPolicy
            $this->authorize('update', [User::class, JWTAuth::parseToken()->authenticate()]);


            $validator = Validator::make($request->all(), [
                'username' => 'nullable|string|max:255|unique:users,username,' . $user->id, // Exclude current user by ID
                'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id, // Exclude current user by ID
                'password' => 'nullable|string|min:8|confirmed',
                'roles' => 'nullable|array', // Ensure roles are required and at least one role is provided
                'roles.*' => 'exists:roles,id', // Validate each role exists in the roles table
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422); // 422 Unprocessable Entity
            }

            $roleIds = $request->input('roles');
            if ($roleIds) {
                // Detach all existing roles
                $user->roles()->detach();


                // converting the roleNames to array if only one role send
                $roleIds = is_array($roleIds) ? $roleIds : [$roleIds];
                foreach ($roleIds as $roleId) {
                    // assigning new roles
                    $user->assignRole($roleId);
                }
            }

            // update the user
            $user->update([
                'username' => $request->filled('username') ? $request->input('username') : $user->username,
                'email' => $request->filled('email') ? $request->input('email') : $user->email,
                'roles' => $request->filled('roles') ? $request->input('roles') : ($user->roles),
                'password' => $request->filled('password') ? Hash::make($request->input('password')) : $user->password,
            ]);


            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => new UserResource($user)
            ], 200); // 200 OK

        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage(),
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred :' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            // check if user has permission to delete  using UserPolicy
            $this->authorize('delete', User::class);

            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully',
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage(),
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred :' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display all deleted users.
     *
     * @return \Illuminate\Http\Response
     */
    public function trashed()
    {
        try {
            // check if user has permission to view deleted users  using UserPolicy
            $this->authorize('viewTrashed', User::class);

            $deletedUsers = UserResource::collection(User::onlyTrashed()->paginate(10));
            return response()->json([
                'status' => 'success',
                'data' => UserResource::collection($deletedUsers),
                'pagination' => [
                    'total' => $deletedUsers->total(),
                    'per_page' => $deletedUsers->perPage(),
                    'current_page' => $deletedUsers->currentPage(),
                    'last_page' => $deletedUsers->lastPage(),
                    'next_page_url' => $deletedUsers->nextPageUrl(),
                    'prev_page_url' => $deletedUsers->previousPageUrl(),
                ]
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage(),
            ], 403); // 403 Forbidden
        }
        // problem encountred while getting users
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred :' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }
    /**
     * Restore a deleted user.
     *
     * @param  $user
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            // check if user has permission to view deleted users  using UserPolicy
            $this->authorize('restore', User::class);

            $restoredUser = User::withTrashed()->findOrFail($id);
            $restoredUser->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'User restored.',
                'data' => new UserResource($restoredUser)
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage(),
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }
        // problem encountred while getting users
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred :' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Force delete a specific role.
     *
     * @param  $user
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        try {
            // check if user has permission to force deleted user using UserPolicy
            $this->authorize('forceDelete', User::class);
            $user = User::onlyTrashed()->findOrFail($id);

            $user->roles()->detach();
            $user->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'User permanently deleted.',
            ], 200);
        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage(),
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }
        // problem encountred while getting users
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred :' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }
}
