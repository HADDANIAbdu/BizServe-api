<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\InteractionResource;
use App\Models\Client;
use App\Models\Interaction;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InteractionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // check if user has permission
            $this->authorize('viewAny', Interaction::class);


            $interactions = Interaction::paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => InteractionResource::collection($interactions),
                'pagination' => [
                    'total' => $interactions->total(),
                    'per_page' => $interactions->perPage(),
                    'current_page' => $interactions->currentPage(),
                    'last_page' => $interactions->lastPage(),
                    'next_page_url' => $interactions->nextPageUrl(),
                    'prev_page_url' => $interactions->previousPageUrl(),
                ]
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403);
        } catch (\Throwable $th) {

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $this->authorize('create', Interaction::class);


            $validator = Validator::make($request->all(), [
                'client_id' => 'required|integer',
                'service_id' => 'required|integer',
                'type' => 'required|string|in:call,meet,email,other',
                'date_interaction' => 'required|date',
                'outcome' => 'required|string',
                'details' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' =>  $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            // check if user exist
            if (!Client::where('id', $request->input('client_id'))->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client does not exist',
                ], 404); // 404 Not Found.
            }
            // check if service exist
            if (!Service::where('id', $request->input('service_id'))->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service does not exist',
                ], 404); // 404 Not Found.
            }

            // $interaction = Interaction::create($request->only(['client_id', 'service_id', 'type', 'date_interaction', 'outcome', 'details']));
            // creating the notification
            $interaction = Interaction::create([
                'client_id' => $request->input('client_id'),
                'service_id' => $request->input('service_id'),
                'type' => $request->input('type'),
                'outcome' => $request->input('outcome'),
                'details' => $request->input('details'),
                'date_interaction' => $request->input('date_interaction') ? Carbon::parse($request->input('date_interaction'))->format('Y-m-d H:i:s') : Carbon::now(),
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Interaction created successfully',
                'data' => new InteractionResource($interaction)
            ], 201);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while creating the interaction ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Interaction $interaction)
    {
        try {

            $this->authorize('view', Interaction::class);

            return response()->json([
                'status' => 'success',
                'message' => 'Interaction details retrieved successfully',
                'data' => new InteractionResource($interaction)
            ], 200); // 200 OK
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
    public function update(Request $request, Interaction $interaction)
    {
        try {
            $this->authorize('update', Interaction::class);



            $validator = Validator::make($request->all(), [
                'type' => 'nullable|string|in:call,meet,email,other',
                'date_interaction' => 'nullable|date',
                'outcome' => 'nullable|string',
                'details' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' =>  $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }
            $interaction->update($request->only(['type', 'details', 'date_interaction', 'outcome', 'details']));

            return response()->json([
                'status' => 'success',
                'message' => 'Interaction updated successfully',
                'data' => new InteractionResource($interaction)
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while updating the interaction ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Interaction $interaction)
    {
        try {
            $this->authorize('delete', Interaction::class);


            $interaction->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Interaction deleted successfully'
            ], 200); // 200 OK

        } catch (AuthorizationException $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Interaction not found.'
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while deleting the interaction ' . $th->getMessage()
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
            // check if user has permission to view deleted interactions using InteractionPolicy
            $this->authorize('viewTrashed', Interaction::class);

            $deletedInteractions = InteractionResource::collection(Interaction::onlyTrashed()->paginate(10));
            return response()->json([
                'status' => 'success',
                'data' => InteractionResource::collection($deletedInteractions),
                'pagination' => [
                    'total' => $deletedInteractions->total(),
                    'per_page' => $deletedInteractions->perPage(),
                    'current_page' => $deletedInteractions->currentPage(),
                    'last_page' => $deletedInteractions->lastPage(),
                    'next_page_url' => $deletedInteractions->nextPageUrl(),
                    'prev_page_url' => $deletedInteractions->previousPageUrl(),
                ]
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
                'message' => 'Interaction not found.',
            ], 404);
        }
        // problem encountred while getting deleted services
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
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
            // check if user has permission to restore interactions using InteractionPolicy
            $this->authorize('restore', Interaction::class);

            $restoredInteraction = Interaction::withTrashed()->findOrFail($id);
            $restoredInteraction->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'service restored.',
                'data' => new InteractionResource($restoredInteraction)
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
                'message' => 'Interaction not found.',
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
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        try {
            // check if user has permission to force deleted user using UserPolicy
            $this->authorize('forceDelete', Interaction::class);
            $interaction = Interaction::onlyTrashed()->findOrFail($id);


            $interaction->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Interaction permanently deleted.',
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
                'message' => 'Interaction not found.',
            ], 404); // 404 Not Found
        }
        // problem encountred while force deleting service
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    public function getSummary(Request $request)
    {
        try {
            // check if user has permission
            $this->authorize('summary', Interaction::class);
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now();

            $interactions = Interaction::whereBetween('date_interaction', [$startDate, $endDate])->get();

            return response()->json([
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'data' => InteractionResource::collection($interactions)
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
                'message' => 'Interaction not found.',
            ], 404); // 404 Not Found
        }
        // problem encountred while force deleting service
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }


    public function getUpcomming()
    {
        try {
            // check if user has permission
            $this->authorize('upcomming', Interaction::class);
            $interactions = Interaction::where('date_interaction', '>', Carbon::today())->get();


            return response()->json([
                'data' => InteractionResource::collection($interactions)
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
                'message' => 'Interaction not found.',
            ], 404); // 404 Not Found
        }
        // problem encountred while force deleting service
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
}
