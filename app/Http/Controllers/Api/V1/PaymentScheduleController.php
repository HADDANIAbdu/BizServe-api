<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PaymentScheduleResource;
use Illuminate\Support\Facades\Validator;
use App\Models\Client;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class PaymentScheduleController extends Controller
{
    /**
     *
     *return list of payment schedule as array for each payment
     */
    public function index(Client $client)
    {
        try {
            // check if user has permission
            $this->authorize('viewAny', PaymentSchedule::class);

            $payments = $client->payments;

            $schedules = [];
            if ($payments && $payments->count()) {
                foreach ($payments as $payment) {
                    $paymentSchedule =  $payment->paymentSchedules;

                    $paymentSchedule = PaymentScheduleResource::collection($paymentSchedule);

                    $schedules[] = ['service' => $payment->service_id, 'schedules' => $paymentSchedule];
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Schedules retrieved successfully',
                    'data' => ($schedules)
                ], 200);
            } else
                return response()->json([
                    'status' => 'error',
                    'message' => 'no payment found for this client',
                ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while creating the user ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }


    /**
     * Store a newly created schedule .
     */
    public function store(Request $request, Client $client)
    {
        try {
            // check if user has permission
            $this->authorize('create', PaymentSchedule::class);

            $payments = $client->payments;

            // check if client has payments (cant create a payment schedule without payment)
            if (($payments->isEmpty())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cant create a payment schedule without payment',
                ], 401); // 401 
            }

            // validate the request 
            $validator = Validator::make($request->all(), [
                'payment_id' => 'required|numeric|max:255|exists:payments,id',
                'amount' => 'required|numeric',
                'due_date' => 'required|date',
                'status' => 'nullable|string|in:pending,completed,overdue',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' =>  $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            // get the payment to check if the amount in schedule is valid
            $payment = Payment::where('id', $request->input('payment_id'))->firstOrFail();
            // get the total amount that has been scheduled 
            $scheduled_amount = 0;
            foreach ($payment->paymentSchedules as $paymentSchedule) {
                $scheduled_amount += $paymentSchedule->amount;
            }
            // get the remained amount 
            $remained_amount = $payment->total_amount - $scheduled_amount;

            if ($remained_amount < $request->input('amount')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The amount should not exceed ' . $remained_amount,
                ], 401);
            }

            $paymentSchedule = PaymentSchedule::create([
                'payment_id' =>  $request->input('payment_id'),
                'amount' => $request->input('amount'),
                'due_date' => $request->input('due_date'),
                'status' => $request->input('status') ? $request->input('status') : 'pending',
            ]);


            return response()->json([
                'status' => 'success',
                'message' => 'Schedule created successfully',
                'data' => new PaymentScheduleResource($paymentSchedule)
            ], 201); // 201 Created

        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden 
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while creating the schedule ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client, PaymentSchedule $paymentSchedule)
    {
        try {
            // check if user has permission
            $this->authorize('update', PaymentSchedule::class);

            if (!$paymentSchedule) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'payment schedule not found',
                ], 404);
            }
            // validate the request 
            $validator = Validator::make($request->all(), [
                'payment_id' => 'numeric|max:255|exists:payments,id',
                'amount' => 'numeric|max:255',
                'due_date' => 'date',
                'status' => 'string|in:pending,completed,overdue',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' =>  $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }



            $paymentSchedule->update([
                'payment_id' =>  $request->input('payment_id') ? $request->input('payment_id') : $paymentSchedule->payment_id,
                'amount' => $request->input('amount') ? $request->input('amount') : $paymentSchedule->amount,
                'due_date' => $request->input('due_date') ? $request->input('due_date') : $paymentSchedule->due_date,
                'status' => $request->input('status') ? $request->input('status') : $paymentSchedule->status,
            ]);


            return response()->json([
                'status' => 'success',
                'message' => 'Schedule updated successfully',
                'data' => new PaymentScheduleResource($paymentSchedule)
            ], 200); // 201 Created

        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while creating the user ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
}
