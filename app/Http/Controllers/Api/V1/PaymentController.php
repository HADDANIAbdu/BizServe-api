<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PaymentResource;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            // check if user has permission
            $this->authorize('viewAny', Payment::class);

            $payments = Payment::paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => PaymentResource::collection($payments),
                'pagination' => [
                    'total' => $payments->total(),
                    'per_page' => $payments->perPage(),
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'next_page_url' => $payments->nextPageUrl(),
                    'prev_page_url' => $payments->previousPageUrl(),
                ]
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403);
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
            $this->authorize('create', Payment::class);

            $validator = Validator::make($request->all(), [
                'client_id' => 'required|integer',
                'service_id' => 'required|integer',
                'total_amount' => 'required|numeric',
                // 'type' => 'required|in:onetime,monthly,annualy',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }
            // check if user exists
            if (!Client::where('id', $request->input('client_id'))->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client does not exist',
                ], 404); // 404 Not Found.
            }

            // check if service exists
            if (!Service::where('id', $request->input('service_id'))->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service does not exist',
                ], 404); // 404 Not Found.
            }

            $payment = Payment::create([
                'client_id' => $request->input('client_id'),
                'service_id' => $request->input('service_id'),
                'total_amount' => $request->input('total_amount')
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment created successfully',
                'data' => new PaymentResource($payment)
            ], 201); // 201 Created
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while creating the payment' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        try {

            // check if user has permission
            $this->authorize('view', Payment::class);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment details retrieved successfully',
                'data' => new PaymentResource($payment)
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
    public function update(Request $request, Payment $payment)
    {
        try {
            $this->authorize('update', Payment::class);


            $validator = Validator::make($request->all(), [
                'client_id' => 'nullable|integer',
                'service_id' => 'nullable|integer',
                'total_amount' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }
            $client_id = $request->input('client_id') ? $request->input('client_id') : $payment->client_id;
            $service_id = $request->input('service_id') ? $request->input('service_id') : $payment->service_id;
            $total_amount = $request->input('total_amount') ? $request->input('total_amount') : $payment->total_amount;

            // check if user exists
            if (!Client::where('id', $client_id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client does not exist',
                ], 404); // 404 Not Found.
            }
            // check if service exists
            if (!Service::where('id', $service_id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service does not exist',
                ], 404); // 404 Not Found.
            }

            $payment->update([
                'client_id' => $client_id,
                'service_id' => $service_id,
                'total_amount' => $total_amount,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment updated successfully',
                'data' => new PaymentResource($payment)
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while updating the payment ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        try {
            $this->authorize('delete', Payment::class);

            $payment->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment deleted successfully'
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment does not exist.',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while deleting the payment ' . $th->getMessage()
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
            // check if user has permission to view deleted payments  using paymentPolicy
            $this->authorize('viewTrashed', Payment::class);

            $deletedPayments = PaymentResource::collection(Payment::onlyTrashed()->paginate(10));
            return response()->json([
                'status' => 'success',
                'data' => PaymentResource::collection($deletedPayments),
                'pagination' => [
                    'total' => $deletedPayments->total(),
                    'per_page' => $deletedPayments->perPage(),
                    'current_page' => $deletedPayments->currentPage(),
                    'last_page' => $deletedPayments->lastPage(),
                    'next_page_url' => $deletedPayments->nextPageUrl(),
                    'prev_page_url' => $deletedPayments->previousPageUrl(),
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
                'message' => 'Payment does not exist.',
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
            // check if user has permission to restore payments  using paymentPolicy
            $this->authorize('restore', Payment::class);

            $restoredPayment = Payment::withTrashed()->findOrFail($id);
            $restoredPayment->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'service restored.',
                'data' => new PaymentResource($restoredPayment)
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
                'message' => 'Payment does not exist.',
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
            $this->authorize('forceDelete', Payment::class);
            $payment = Payment::onlyTrashed()->findOrFail($id);


            $payment->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment permanently deleted.',
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
                'message' => 'Payment does not exist.',
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
}
