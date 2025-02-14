<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PaymentReminderResource;
use App\Models\Client;
use App\Models\Payment;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentReminder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class PaymentReminderController extends Controller
{
    // jobs
    public function reminder(Request $request, Client $client)
    {
        try {
            // check if user has permission
            $this->authorize('reminder', Payment::class);

            // validate the request 
            $validator = Validator::make($request->all(), [
                'message' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' =>  $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            $paymentReminder = PaymentReminder::create([
                'client_id' =>  $client->id,
                'message' =>  $request->input('message')
            ]);


            return response()->json([
                'status' => 'success',
                'message' => 'PaymentReminder created successfully',
                'data' => new PaymentReminderResource($paymentReminder)
            ], 201); // 201 Created

        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while creating the payment reminder',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
    public function history(Client $client)
    {
        try {
            // check if user has permission
            $this->authorize('history', Payment::class);


            $paymentReminder = PaymentReminder::where('client_id', $client->id)->get();

            if (!$paymentReminder) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'payment Reminder does not exist for this user',
                ], 404); // 404 Not Found.
            }

            return response()->json([
                'status' => 'success',
                'message' => 'PaymentReminder created successfully',
                'data' => PaymentReminderResource::collection($paymentReminder)
            ], 201); // 201 Created

        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while creating the payment reminder',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
}
