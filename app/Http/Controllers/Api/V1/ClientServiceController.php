<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ServiceResource;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ClientServiceController extends Controller
{

    /**
     * client enroll a service
     */
    public function enrollService(Request $request, Client $client)
    {
        try {
            $this->authorize('enrollService', Client::class);

            $validator = Validator::make($request->all(), [
                'serviceId' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' =>  $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            $service = Service::findOrFail($request->serviceId);

            if (($client->services)->find($service)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service already enrolled',
                ], 401); // 401 Bad request
            }
            if ($service) {
                // Enroll client in the service
                $client->services()->attach($service, ['enrollement_date' => Carbon::now(), 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);

                Payment::create([
                    'client_id' => $client->id,
                    'service_id' => $service->id,
                    'total_amount' =>  $service->price,

                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Client enrolled in service successfully'
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service not found'
                ], 404);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client or Service not found.' . $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while enrolling client in service' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * get all service that a client enroll
     */
    public function getServices(Client $client)
    {
        try {
            $this->authorize('getEnrolledServices', Client::class);

            // Enroll client in the service
            $clientServices = $client->services()->whereNull('client_service.deleted_at')->paginate(10);

            return response()->json([
                'status' => 'success',
                'message' => ' enrolled in service successfully',
                'data' => ServiceResource::collection($clientServices),
                'pagination' => [
                    'total' => $clientServices->total(),
                    'per_page' => $clientServices->perPage(),
                    'current_page' => $clientServices->currentPage(),
                    'last_page' => $clientServices->lastPage(),
                    'next_page_url' => $clientServices->nextPageUrl(),
                    'prev_page_url' => $clientServices->previousPageUrl(),
                ]
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
                'message' => 'Client or Service not found.' . $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while enrolling client in service' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * remove a specific service for a client permenatly
     */
    public function removeService(Client $client, Service $service)
    {
        try {
            $this->authorize('removeEnrolledService', Client::class);

            $client->softDetachService($service->id);
            Payment::where('client_id', $client->id)->where('service_id', $service->id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Service removed successfully.'
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
                'message' => 'Client or Service not found.' . $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while enrolling client in service' . $th->getMessage()
            ], 500);
        }
    }
    /**
     * remove a specific service for a client permenatly
     */
    public function forceRemoveService(Client $client, Service $service)
    {
        try {
            $this->authorize('forceRemoveEnrolledService', Client::class);

            $client->services()->detach($service->id);
            Payment::where('client_id', $client->id)->where('service_id', $service->id)->onlyTrashed()->forcedelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Service forced removed successfully.'
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
                'message' => 'Client or Service not found.' . $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while enrolling client in service' . $th->getMessage()
            ], 500);
        }
    }
}
