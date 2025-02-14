<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        try {

            // check if user has permission to diplay all roles using RolePolicy
            $this->authorize('viewAny', Role::class);

            // Load roles with users
            $roles = Role::all();
            // $roles = Role::paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => RoleResource::collection($roles),
                // 'pagination' => [
                //     'total' => $roles->total(),
                //     'per_page' => $roles->perPage(),
                //     'current_page' => $roles->currentPage(),
                //     'last_page' => $roles->lastPage(),
                //     'next_page_url' => $roles->nextPageUrl(),
                //     'prev_page_url' => $roles->previousPageUrl(),
                // ]
            ], 200);
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
                'message' => 'An error occurred ' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }
    /**
     * Display a listing of roles.
     */
    public function permissions()
    {
        try {

            // check if user has permission to diplay all roles using RolePolicy
            $this->authorize('viewAny', Role::class);

            $permissions = Permission::all();
            // Group permissions by context 
            $groupedPermissions = $permissions->groupBy('context')->map(function ($group) {
                return $group->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                });
            });

            return response()->json([
                'status' => 'success',
                'data' => $groupedPermissions
            ], 200);
        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        }
        // problem encountred while getting users
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        try {
            // Check if the user has permission to create a role
            $this->authorize('create', Role::class);

            // validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles,name',
                'permissions' => 'required|array', // Ensure permissions are required and at least one role is provided
                'permissions.*' => 'exists:permissions,id', // Validate each role exists in the permissions table
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' =>  $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }
            // creating role
            $role = Role::create([
                'name' => $request->input('name'),
            ]);

            $permissionsIds = $request->input('permissions');
            if ($permissionsIds) {
                // Convert the permissions to an array if it's not already one
                $permissionsIds = is_array($permissionsIds) ? $permissionsIds : [$permissionsIds];

                // Sync the permissions with the role
                $role->permissions()->sync($permissionsIds);
            }


            return response()->json([
                'status' => 'success',
                'message' => 'Role created successfully',
                'data' => new RoleResource($role)
            ], 201); // 201 Created
        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403);
        }
        // problem encountred while creating role
        catch (\Throwable $th) {

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        try {

            // check if user has permission to diplay a specific role 
            $this->authorize('view', Role::class);

            return response()->json([
                'status' => 'success',
                'message' => 'Role details retrieved successfully',
                'data' => new RoleResource($role)
            ], 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {

        try {
            // check if user has permission to modify on a role
            $this->authorize('update', Role::class);

            // validate that the name is unique, the permissions array can be null
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255|unique:roles,name, ' . $role->id,
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,id', // Validate each role exists in the permissions table
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' =>  $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }


            $permissionsIds = $request->input('permissions');
            if ($permissionsIds) {
                $role->permissions()->sync($permissionsIds);
            }
            // update role
            $role->update([
                'name' => $request->input('name') ? $request->input('name') :  $role->name,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Role updated successfully',
                'data' => new RoleResource($role)
            ], 200); // 200 OK 
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found.',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred. ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        try {
            $this->authorize('delete', Role::class);

            // soft delete role
            $role->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Role deleted successfully'
            ], 200); // 200 OK

        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display all deleted roles.
     *
     * @return \Illuminate\Http\Response
     */
    public function trashed()
    {
        try {
            // check if user has permission to view deleted roles  using RolePolicy
            $this->authorize('viewTrashed', Role::class);

            $deletedRoles = Role::onlyTrashed()->paginate(10);
            return response()->json([
                'status' => 'success',
                'data' => RoleResource::collection($deletedRoles),
                'pagination' => [
                    'total' => $deletedRoles->total(),
                    'per_page' => $deletedRoles->perPage(),
                    'current_page' => $deletedRoles->currentPage(),
                    'last_page' => $deletedRoles->lastPage(),
                    'next_page_url' => $deletedRoles->nextPageUrl(),
                    'prev_page_url' => $deletedRoles->previousPageUrl(),
                ]
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        }
        // problem encountred while getting users
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
    /**
     * Restore a deleted role.
     *
     * @param  $user
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            // check if user has permission to view deleted users  using RolePolicy
            $this->authorize('restore', Role::class);

            // get the role
            $restoredRole = Role::withTrashed()->findOrFail($id);
            $restoredRole->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Role restored.',
                'data' => new RoleResource($restoredRole)
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found.',
            ], 404);
        }
        // problem encountred while getting users
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Force delete a specific role.
     *
     * @param  $role
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        try {
            // check if user has permission to force deleted role using RolePolicy
            $this->authorize('forceDelete', Role::class);
            $role = Role::onlyTrashed()->findOrFail($id);

            $role->users()->detach();
            $role->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Role permanently deleted.',
            ], 200);
        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found.',
            ], 404);
        }
        // problem encountred while getting users
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
}
