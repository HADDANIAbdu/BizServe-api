<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\NotificationResource;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\PaymentSchedule;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        try {

            // check if user has permission to diplay all notifications using NotificationPolicy
            $this->authorize('viewAny', Notification::class);

            // Load notifications
            $notifications = Notification::paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => NotificationResource::collection($notifications),
                'pagination' => [
                    'total' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'next_page_url' => $notifications->nextPageUrl(),
                    'prev_page_url' => $notifications->previousPageUrl(),
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
        // problem encountred while getting notifications
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Store a newly created notification.
     */
    public function store(Request $request)
    {
        try {
            // check if user has permission to create a new notification using NotificationPolicy
            $this->authorize('create', Notification::class);

            // validate the request 
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|numeric',
                'type' => 'nullable|string|max:255',
                'data' => 'required|string',
                'sent_at' => 'nullable|date',
                'read_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            // check if user exist
            if (!Client::where('id', $request->input('client_id'))->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client does not exist',
                ], 404); // 404 Not Found.
            }

            // creating the notification
            $notification = Notification::create([
                'client_id' => $request->input('client_id'),
                'type' => $request->input('type'),
                'data' => json_encode($request->input('data'), true),
                'sent_at' => $request->input('sent_at') ? Carbon::parse($request->input('sent_at'))->format('Y-m-d H:i:s') : Carbon::now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification created successfully',
                'data' => new NotificationResource($notification)
            ], 201); // 201 Created

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        }
        // problem encountred while creating notification
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while creating the notification ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display the specified notification.
     */
    public function show(notification $notification)
    {
        try {
            // check if user has permission to diplay a specific notification using NotificationPolicy
            $this->authorize('view', Notification::class);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification details retrieved successfully',
                'data' => new NotificationResource($notification)
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
    public function update(Request $request, notification $notification)
    {
        try {
            // check if user has permission to update using NotificationPolicy
            $this->authorize('update', Notification::class);


            $validator = Validator::make($request->all(), [
                'client_id' => 'numeric',
                'type' => 'string|max:255',
                'data' => 'string',
                'sent_at' => 'date',
                'read_at' => 'date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            // check if user exist
            if (!Client::where('id', $request->input('client_id'))->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client does not exist',
                ], 404); // 404 Not Found.
            }

            // Update the notification with provided data
            $notification->update([
                'client_id' => $request->input('client_id', $notification->client_id),
                'type' => $request->input('type', $notification->type),
                'data' => $request->filled('data') ? json_encode($request->input('data')) : $notification->data,
                'sent_at' => $request->input('sent_at') ? Carbon::parse($request->input('sent_at'))->format('Y-m-d H:i:s') : $notification->sent_at,
                'read_at' => $request->input('read_at') ? Carbon::parse($request->input('read_at'))->format('Y-m-d H:i:s') : $notification->read_at,
            ]);


            return response()->json([
                'status' => 'success',
                'message' => 'Notification updated successfully',
                'data' => new NotificationResource($notification)
            ], 200); // 200 OK

        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found.',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while updating the notification ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(notification $notification)
    {
        try {
            // check if user has permission to delete  using NotificationPolicy
            $this->authorize('delete', Notification::class);

            $notification->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification deleted successfully',
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found.',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while deleting the notification ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display all deleted notifications.
     *
     * @return \Illuminate\Http\Response
     */
    public function trashed()
    {
        try {
            // check if user has permission to view deleted notifications  using NotificationPolicy
            $this->authorize('viewTrashed', Notification::class);

            $deletedNotifications = NotificationResource::collection(Notification::onlyTrashed()->paginate(10));
            return response()->json([
                'status' => 'success',
                'data' => NotificationResource::collection($deletedNotifications),
                'pagination' => [
                    'total' => $deletedNotifications->total(),
                    'per_page' => $deletedNotifications->perPage(),
                    'current_page' => $deletedNotifications->currentPage(),
                    'last_page' => $deletedNotifications->lastPage(),
                    'next_page_url' => $deletedNotifications->nextPageUrl(),
                    'prev_page_url' => $deletedNotifications->previousPageUrl(),
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
        // problem encountred while getting notifications
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
    /**
     * Restore a deleted notification.
     *
     * @param  $notification
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            // check if user has permission to view deleted notifications  using NotificationPolicy
            $this->authorize('restore', Notification::class);

            $restoredNotification = Notification::withTrashed()->findOrFail($id);
            $restoredNotification->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification restored.',
                'data' => new NotificationResource($restoredNotification)
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
                'message' => 'Notification not found.',
            ], 404);
        }
        // problem encountred while getting notifications
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
     * @param  $notification
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        try {
            // check if user has permission to force deleted notification using NotificationPolicy
            $this->authorize('forceDelete', Notification::class);
            $notification = Notification::onlyTrashed()->findOrFail($id);

            $notification->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification permanently deleted.',
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
                'message' => 'Notification not found.',
            ], 404);
        }
        // problem encountred while getting notifications
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred ' . $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }


    public function autoNotification(Request $request)
    {
        $paymentSchedules = PaymentSchedule::where();
        $schedules = Schedule::where();
    }
}
