<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ClientResource;
use App\Http\Resources\V1\InteractionResource;
use App\Http\Resources\V1\NotificationResource;
use App\Http\Resources\V1\PaymentResource;
use App\Http\Resources\V1\ScheduleResource;
use App\Http\Resources\V1\ServiceResource;
use App\Models\Client;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // check if user has permission to diplay all clients using ClientPolicy
            $this->authorize('viewAny', Client::class);

            // load all clients with services enrolled using the ClientResourse
            $clients = Client::paginate(6);


            return response()->json([
                'status' => 'success',
                'data' => ClientResource::collection($clients),
                'pagination' => [
                    'total' => $clients->total(),
                    'per_page' => $clients->perPage(),
                    'current_page' => $clients->currentPage(),
                    'last_page' => $clients->lastPage(),
                    'next_page_url' => $clients->nextPageUrl(),
                    'prev_page_url' => $clients->previousPageUrl(),
                ]
            ], 200); // 200 ok
        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage(),
            ], 403); // 403 Forbidden
        }
        // problem encountred while getting clients
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Check if user has permission to create a new client using ClientPolicy
            $this->authorize('create', Client::class);

            // Validate request
            $validator = Validator::make($request->all(), [
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'phone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
                'preference' => 'nullable|string',
                'password' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error' . $validator->errors(),
                ], 422); // 422 Unprocessable Entity
            }

            // Generate default password if none provided
            $password = $request->input('password') ?? $request->input('firstname') . $request->input('lastname');

            // Hash the password
            $hashedPassword = bcrypt($password);

            // Create client
            $client = Client::create($request->only(['firstname', 'lastname', 'email', 'phone', 'preference']) + ['password' => $hashedPassword]);

            return response()->json([
                'status' => 'success',
                'message' => 'Client created successfully',
                'data' => new ClientResource($client)
            ], 201); // 201 Created
        }
        // User doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage(),
            ], 403); // 403 Forbidden
        }
        // Problem encountered while creating client
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while creating a client' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        try {
            // check if user has permission to diplay a specific client using ClientPolicy
            $this->authorize('view', Client::class);

            // Retrieve related data
            $services = $client->services()->get(); // Ensure you use ->get()
            $schedules = $client->schedules()->get();
            $payments = $client->payments()->get();
            $notifications = $client->notifications()->get();
            $interactions = $client->interactions()->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Client details retrieved successfully',
                'data' => [
                    'client' => new ClientResource($client),
                    'services' => ServiceResource::collection($services),
                    'schedules' => ScheduleResource::collection($schedules),
                    'payments' => PaymentResource::collection($payments),
                    'notifications' => NotificationResource::collection($notifications),
                    'interactions' => InteractionResource::collection($interactions),
                ]
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage(),
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        try {
            // check if user has permission to update using ClientPolicy
            $this->authorize('update', Client::class);


            $validator = Validator::make($request->all(), [
                'firstname' => 'nullable|string|max:255',
                'lastname' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255',
                'phone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
                'preference' => 'nullable|string',
                'password' => 'nullable|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error ' . $validator->errors(),
                ], 422); // 422 Unprocessable Entity
            }

            if ($request->input('password')) {
                $password = $request->input('password');
                $hashedPassword = bcrypt($password);
            } else
                $hashedPassword = $client->password;

            // update the client
            $client->update($request->only(['firstname', 'lastname', 'email', 'phone','preference']) + ['password' => $hashedPassword]);

            return response()->json([
                'status' => 'success',
                'message' => 'Client updated successfully',
                'data' =>  new ClientResource($client)
            ], 200); // 200 OK

        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage(),
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found.',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while updating the client' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        try {
            // check if user has permission
            $this->authorize('delete', Client::class);

            foreach ($client->interactions as $interaction) {
                $interaction->delete();
            }
            foreach ($client->schedules as $schedule) {
                $schedule->delete();
            }
            foreach ($client->payments as $payment) {
                $payment->delete();
            }
            foreach ($client->notifications as $notification) {
                $notification->delete();
            }


            foreach ($client->services as $service) {
                $client->softDetachService($service->id);
            }


            $client->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Client deleted successfully'
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage(),
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found.',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while deleting the client' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Get all clients that have the same first name and last name, or the same email, or the same phone number.
     */
    public function getDuplicates()
    {
        try {
            // Fetch all clients from the database
            $clients = Client::all();

            // Initialize an empty array for duplicates
            $duplicates = [];

            // Group by firstname and lastname
            $firstnameLastnameDuplicates = $clients->groupBy(function ($client) {
                return $client->firstname . ' ' . $client->lastname;
            })->filter(function ($group) {
                return $group->count() > 1;
            })->flatMap(function ($group) {
                return $group;
            });

            // Group by email
            $emailDuplicates = $clients->groupBy('email')->filter(function ($group) {
                return $group->count() > 1;
            })->flatMap(function ($group) {
                return $group;
            });

            // Group phone
            $phoneDuplicates = $clients->groupBy('phone')->filter(function ($group) {
                return $group->count() > 1;
            })->flatMap(function ($group) {
                return $group;
            });

            // Assign duplicates to the duplicates array
            $duplicates['firstnameLastname'] = ClientResource::collection($firstnameLastnameDuplicates);
            $duplicates['email'] = ClientResource::collection($emailDuplicates);
            $duplicates['phone'] = ClientResource::collection($phoneDuplicates);

            // Check if there are any duplicates found
            $duplicatesFound = $firstnameLastnameDuplicates->isNotEmpty() || $emailDuplicates->isNotEmpty() || $phoneDuplicates->isNotEmpty();

            return response()->json([
                'status' => 'success',
                'message' => $duplicatesFound ? 'Duplicates ' : 'No duplicates found',
                'data' => $duplicates
            ], 200); // 200 OK
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
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
            // check if user has permission to view deleted clients  using ClientPolicy
            $this->authorize('viewTrashed', Client::class);

            $deletedClients = ClientResource::collection(Client::onlyTrashed()->paginate(10));
            return response()->json([
                'status' => 'success',
                'data' => ClientResource::collection($deletedClients),
                'pagination' => [
                    'total' => $deletedClients->total(),
                    'per_page' => $deletedClients->perPage(),
                    'current_page' => $deletedClients->currentPage(),
                    'last_page' => $deletedClients->lastPage(),
                    'next_page_url' => $deletedClients->nextPageUrl(),
                    'prev_page_url' => $deletedClients->previousPageUrl(),
                ]
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage(),
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found.',
            ], 404);
        }
        // problem encountred while getting deleted clients
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
    /**
     * Restore a deleted user.
     *
     * @param  $client
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            // check if user has permission to restore clients  using ClientPolicy
            $this->authorize('restore', Client::class);

            $restoredClient = Client::withTrashed()->findOrFail($id);
            $restoredClient->restore();

            $restoredClient->interactions()->withTrashed()->restore();
            $restoredClient->schedules()->withTrashed()->restore();
            $restoredClient->payments()->withTrashed()->restore();
            $restoredClient->notifications()->withTrashed()->restore();


            foreach ($restoredClient->services as $service) {
                $restoredClient->restoreService($service->id);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Client restored.',
                'data' => new ClientResource($restoredClient)
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage(),
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found.',
            ], 404);
        }
        // problem encountred while getting users
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred' . $th->getMessage(),
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
            $this->authorize('forceDelete', Client::class);
            $client = Client::onlyTrashed()->findOrFail($id);


            $client->services()->detach();

            $client->interactions()->delete();
            $client->schedules()->delete();
            $client->payments()->delete();
            $client->notifications()->delete();

            $client->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'User permanently deleted.',
            ], 200);
        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage(),
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found.',
            ], 404); // 404 Not Found
        }
        // problem encountred while force deleting client
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    public function search(Request $request)
    {

        try {
            // check if user has permission to diplay all clients using ClientPolicy
            $this->authorize('view', Client::class);

            $query = Client::query();

            if ($request->has('firstname') && !empty($request->firstname)) {
                $query->where('firstname', $request->firstname);
            }

            if ($request->has('lastname') && !empty($request->lastname)) {
                $query->where('lastname', $request->lastname);
            }

            if ($request->has('email') && !empty($request->email)) {
                $query->where('email', $request->email);
            }

            if ($request->has('phone') && !empty($request->phone)) {
                $query->where('phone', 'like', '%' . $request->phone . '%');
            }

            if ($request->has('preference') && !empty($request->preference)) {
                $query->where('preference', 'like', '%' . $request->preference . '%');
            }


            // load all clients with services enrolled
            $clients = $query->get();
            if (!$clients->count()) {

                return response()->json([
                    'status' => 'error',
                    'Message' => 'No client found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => ClientResource::collection($clients)
            ], 200); // 200 ok
        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage(),
            ], 403); // 403 Forbidden
        }
        // problem encountred while getting clients
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred' . $th->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }
}
