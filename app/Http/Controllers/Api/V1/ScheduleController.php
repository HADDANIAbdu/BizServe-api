<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ScheduleResource;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Schedule;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{

    /**
     * Display a listing of schedules.
     */
    public function index()
    {
        try {

            // check if user has permission to diplay all schedules using SchedulePolicy
            $this->authorize('viewAny', Schedule::class);

            // Load schedules
            $schedules = Schedule::all();

            return response()->json([
                'status' => 'success',
                'data' => ScheduleResource::collection($schedules)
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        }
        // problem encountred while getting schedules
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Store a newly created schedule.
     */
    public function store(Request $request)
    {
        try {
            // check if user has permission to create a new schedule using SchedulePolicy
            $this->authorize('create', Schedule::class);


            // validate the request 
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|numeric',
                'service_id' => 'required|numeric',
                'type' => 'required|string|max:255',
                'scheduled_at' => 'required|date'
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
            // check if service exist
            if (!Service::where('id', $request->input('service_id'))->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service does not exist',
                ], 404); // 404 Not Found.
            }

            // creating the schedule
            $schedule = Schedule::create([
                'client_id' => $request->input('client_id'),
                'service_id' => $request->input('service_id'),
                'type' => $request->input('type'),
                'scheduled_at' => $request->input('scheduled_at'),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'schedule created successfully',
                'data' => new ScheduleResource($schedule)
            ], 201); // 201 Created

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        }
        // problem encountred while creating schedule
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while creating the schedule',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display the specified schedule.
     */
    public function show(Schedule $schedule)
    {
        try {
            // check if user has permission to diplay a specific schedule using SchedulePolicy
            $this->authorize('view', Schedule::class);

            return response()->json([
                'status' => 'success',
                'message' => 'schedule details retrieved successfully',
                'data' => new ScheduleResource($schedule)
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule)
    {
        try {
            // check if user has permission to update using SchedulePolicy
            $this->authorize('update', Schedule::class);


            $validator = Validator::make($request->all(), [
                'client_id' => 'numeric',
                'service_id' => 'numeric',
                'type' => 'string|max:255',
                'scheduled_at' => 'date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            $client_id = $request->filled('client_id') ? $request->input('client_id') : $schedule->client_id;
            $service_id = $request->filled('service_id') ? $request->input('service_id') : $schedule->service_id;

            // check if user exist
            if (!Client::where('id', $client_id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client does not exist',
                ], 404); // 404 Not Found.
            }
            // check if user exist
            if (!Service::where('id', $service_id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client does not exist',
                ], 404); // 404 Not Found.
            }

            // update the schedule
            $schedule->update([
                'client_id' => $client_id,
                'service_id' => $service_id,
                'type' => $request->filled('type') ? $request->input('type') : $schedule->type,
                'scheduled_at' => $request->filled('scheduled_at') ? $request->input('scheduled_at') : $schedule->scheduled_at,
            ]);


            return response()->json([
                'status' => 'success',
                'message' => 'schedule updated successfully',
                'data' => new ScheduleResource($schedule)
            ], 200); // 200 OK

        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Schedule not found',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while updating the schedule',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        try {
            // check if user has permission to delete  using SchedulePolicy
            $this->authorize('delete', Schedule::class);

            $schedule->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'schedule deleted successfully',
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized ' . $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Schedule not found.',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while deleting the schedule',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display all deleted schedules.
     *
     * @return \Illuminate\Http\Response
     */
    public function trashed()
    {
        try {
            // check if user has permission to view deleted schedules  using SchedulePolicy
            $this->authorize('viewTrashed', Schedule::class);

            $deletedschedules = ScheduleResource::collection(Schedule::onlyTrashed()->paginate(10));
            return response()->json([
                'status' => 'success',
                'data' => ScheduleResource::collection($deletedschedules),
                'pagination' => [
                    'total' => $deletedschedules->total(),
                    'per_page' => $deletedschedules->perPage(),
                    'current_page' => $deletedschedules->currentPage(),
                    'last_page' => $deletedschedules->lastPage(),
                    'next_page_url' => $deletedschedules->nextPageUrl(),
                    'prev_page_url' => $deletedschedules->previousPageUrl(),
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
        // problem encountred while getting schedules
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
    /**
     * Restore a deleted schedule.
     *
     * @param  $schedule
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            // check if user has permission to view deleted schedules  using SchedulePolicy
            $this->authorize('restore', Schedule::class);

            $restoredschedule = Schedule::withTrashed()->findOrFail($id);
            $restoredschedule->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'schedule restored.',
                'data' => new ScheduleResource($restoredschedule)
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
                'message' => 'Schedule not found.',
            ], 404);
        }
        // problem encountred while getting schedules
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Force delete a specific role.
     *
     * @param  $schedule
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        try {
            // check if user has permission to force deleted schedule using SchedulePolicy
            $this->authorize('forceDelete', Schedule::class);
            $schedule = Schedule::onlyTrashed()->findOrFail($id);

            $schedule->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'schedule permanently deleted.',
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
                'message' => 'Schedule not found.',
            ], 404);
        }
        // problem encountred while getting schedules
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }


    public function conflictCheck()
    {
        try {
            // check if user has permission to view deleted schedules  using SchedulePolicy
            $this->authorize('conflictCheck', Schedule::class);

            $schedules = Schedule::select('id', 'client_id', 'service_id', 'scheduled_at')
                ->get()
                ->groupBy(function ($schedule) {
                    return Carbon::parse($schedule->scheduled_at)->toDateString(); // Group by the scheduled date
                })
                ->filter(function ($group) {
                    return $group->count() > 1; // Filter only groups with more than one schedule
                });

            return response()->json([
                'status' => 'success',
                'message' => 'schedule restored.',
                'data' => ($schedules)
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
                'message' => 'Schedule not found.',
            ], 404);
        }
        // problem encountred while getting schedules
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }


    public function conflictResolutions()
    {
        try {

            $this->authorize('conflictResolution', Schedule::class);

            // Retrieve schedules grouped by the same day
            $schedules = Schedule::select('id', 'client_id', 'service_id', 'scheduled_at')
                ->get()
                ->groupBy(function ($schedule) {
                    return Carbon::parse($schedule->scheduled_at)->toDateString(); // Group by the scheduled date
                })
                ->filter(function ($group) {
                    return $group->count() > 1; // Filter only groups with more than one schedule
                });
            // get all the dates where there is at least one schedule
            $dates = Schedule::select('scheduled_at')->get()
                ->groupBy(function ($schedule) {
                    return Carbon::parse($schedule->scheduled_at)->toDateTimeString();
                })->keys()->toArray();

            // loop over each collection that have the same schedule date 
            foreach ($schedules as $date => $group_schedule) {
                // to skip the first schedule within the collection 
                $first_iterator =  true;
                // loop over each collection
                foreach ($group_schedule as $schedule) {
                    // create a new attribut to not modify the schedule itself
                    $schedule->suggested_date = $schedule->scheduled_at;
                    // if it is not the first element in the collection suggets a valid scehdule date
                    if (!$first_iterator) {
                        $dates = $this->suggestResolutions($dates, $schedule);
                        // dd('hello');
                    }
                    // if not skip 
                    else {
                        $first_iterator = false;
                    }
                }
            }


            return response()->json([
                'status' => 'success',
                'data' => $schedules,
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
                'message' => 'Schedule not found.',
            ], 404);
        }
        // problem encountred while getting schedules
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    // function that allow to give a new valid suggested schedule date
    private function suggestResolutions($dates, $schedule)
    {
        // get the date to be changed
        $suggested_date = Carbon::parse($schedule->suggested_date);

        // add a day to suggested date until it is valid
        while (in_array($suggested_date->toDateTimeString(), $dates)) {
            $suggested_date->addDay();
        }
        // add the suggested date to  dates array
        $dates[] = $suggested_date->toDateTimeString();
        // update the schedule suggested date
        $schedule->suggested_date = $suggested_date->toDateTimeString();
        // return the dates to keep the changes
        return $dates;
    }
}
