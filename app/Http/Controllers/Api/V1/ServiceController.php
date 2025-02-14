<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ServiceResource;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            // check if user has permission
            $this->authorize('viewAny', Service::class);

            $services = Service::paginate(10);
            return response()->json([
                'status' => 'success',
                'data' => ServiceResource::collection($services),
                'pagination' => [
                    'total' => $services->total(),
                    'per_page' => $services->perPage(),
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'next_page_url' => $services->nextPageUrl(),
                    'prev_page_url' => $services->previousPageUrl(),
                ]
            ], 200); // 200 OK

        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->authorize('create', Service::class);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'category' => 'required|string',
                'duration' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' =>  $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }
            $service = Service::create([
                'name' => $request->input('name'),
                'price' => $request->input('price'),
                'category' => $request->input('category'),
                'duration' => $request->input('duration'),
                'description' => $request->input('description'),
            ]);


            return response()->json([
                'status' => 'success',
                'message' => 'Service created successfully',
                'data' => new ServiceResource($service)
            ], 201); // 201 Created
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while creating the service' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        try {

            // check if user has permission
            $this->authorize('view', Service::class);

            return response()->json([
                'status' => 'success',
                'message' => 'Service details retrieved successfully',
                'data' => new ServiceResource($service)
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        try {
            $this->authorize('update', Service::class);


            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'description' => 'nullable|string',
                'price' => 'nullable|numeric',
                'category' => 'nullable|string',
                'duration' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' =>  $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }
            $service->update([
                'name' => $request->filled('name') ? $request->input('name') : $service->name,
                'category' => $request->filled('category') ? $request->input('category') : $service->category,
                'price' => $request->filled('price') ? $request->input('price') : $service->price,
                'duration' => $request->filled('duration') ? $request->input('duration') : $service->duration,
                'description' => $request->filled('description') ? $request->input('description') : $service->description,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Service updated successfully',
                'data' => new ServiceResource($service)
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found.',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while updating the service' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        try {
            // check if user has permission
            $this->authorize('delete', Service::class);

            foreach ($service->interactions as $interaction) {
                $interaction->delete();
            }
            foreach ($service->schedules as $schedule) {
                $schedule->delete();
            }
            foreach ($service->payments as $payment) {
                $payment->delete();
            }


            foreach ($service->clients as $client) {
                $service->softDetachClient($client->id);
            }



            $service->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'service deleted successfully'
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found.',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while deleting the service' . $th->getMessage()
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
            // check if user has permission to view deleted services  using servicePolicy
            $this->authorize('viewTrashed', Service::class);

            $deletedServices = serviceResource::collection(Service::onlyTrashed()->paginate(10));
            return response()->json([
                'status' => 'success',
                'data' => serviceResource::collection($deletedServices),
                'pagination' => [
                    'total' => $deletedServices->total(),
                    'per_page' => $deletedServices->perPage(),
                    'current_page' => $deletedServices->currentPage(),
                    'last_page' => $deletedServices->lastPage(),
                    'next_page_url' => $deletedServices->nextPageUrl(),
                    'prev_page_url' => $deletedServices->previousPageUrl(),
                ]
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found.',
            ], 404);
        }
        // problem encountred while getting deleted services
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
    /**
     * Restore a deleted user.
     *
     * @param  $service
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            // check if user has permission to restore services  using servicePolicy
            $this->authorize('restore', Service::class);

            $restoredservice = Service::withTrashed()->findOrFail($id);
            $restoredservice->restore();

            $restoredservice->interactions()->withTrashed()->restore();
            $restoredservice->schedules()->withTrashed()->restore();
            $restoredservice->payments()->withTrashed()->restore();



            foreach ($restoredservice->clients as $client) {
                $restoredservice->restoreClient($client->id);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'service restored.',
                'data' => new serviceResource($restoredservice)
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found.',
            ], 404);
        }
        // problem encountred while getting users
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Force delete a specific role.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        try {
            // check if user has permission to force deleted user using UserPolicy
            $this->authorize('forceDelete', Service::class);
            $service = Service::onlyTrashed()->findOrFail($id);


            $service->clients()->detach();

            $service->interactions()->delete();
            $service->schedules()->delete();
            $service->payments()->delete();


            $service->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Service permanently deleted.',
            ], 200);
        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found.',
            ], 404); // 404 Not Found
        }
        // problem encountred while force deleting service
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    public function search(Request $request)
    {

        try {
            // check if user has permission to diplay all services using servicePolicy
            $this->authorize('view', Service::class);

            $query = Service::query();

            if ($request->has('name') && !empty($request->name)) {
                $query->where('name', $request->name);
            }
            if ($request->has('category') && !empty($request->category)) {
                $query->where('category', $request->category);
            }
            if ($request->has('price') && !empty($request->price)) {
                $query->where('price', '=<', $request->price);
            }

            if ($request->has('keyWord') && !empty($request->keyWord)) {
                $query->where('description', 'like', '%' . $request->keyWord . '%');
            }


            // load all services with services enrolled
            $services = $query->get();
            if (!$services->count()) {

                return response()->json([
                    'status' => 'error',
                    'message' => 'No service found.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => serviceResource::collection($services)
            ], 200); // 200 ok
        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403); // 403 Forbidden
        }
        // problem encountred while getting services
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
}
