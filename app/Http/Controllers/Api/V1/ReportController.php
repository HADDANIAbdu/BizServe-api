<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ReportResource;
use App\Models\report;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Display a listing of reports.
     */
    public function index()
    {
        try {

            // check if user has permission to diplay all reports using ReportPolicy
            $this->authorize('viewAny', Report::class);

            // Load reports with roles
            $reports = Report::paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => ReportResource::collection($reports),
                'pagination' => [
                    'total' => $reports->total(),
                    'per_page' => $reports->perPage(),
                    'current_page' => $reports->currentPage(),
                    'last_page' => $reports->lastPage(),
                    'next_page_url' => $reports->nextPageUrl(),
                    'prev_page_url' => $reports->previousPageUrl(),
                ]
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'errors' => $e->getMessage()
            ], 403); // 403 Forbidden
        }
        // problem encountred while getting reports
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        try {
            // check if user has permission to create a new user using ReportPolicy
            $this->authorize('create', Report::class);

            // validate the request 
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            // creating the report
            $report = Report::create([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'generated_at' => Carbon::now(),
            ]);


            return response()->json([
                'status' => 'success',
                'message' => 'Report created successfully',
                'data' => new ReportResource($report)
            ], 201); // 201 Created

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'errors' => $e->getMessage()
            ], 403); // 403 Forbidden
        }
        // problem encountred while creating report
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while creating the report',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display the specified user.
     */
    public function show(Report $report)
    {
        try {
            // check if user has permission to diplay a specific report using ReportPolicy
            $this->authorize('view', Report::class);

            return response()->json([
                'status' => 'success',
                'message' => 'Report details retrieved successfully',
                'data' => new ReportResource($report)
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'errors' => $e->getMessage()
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
    public function update(Request $request, Report $report)
    {
        try {
            // check if user has permission to update using ReportPolicy
            $this->authorize('update', Report::class);


            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'content' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            // update the user
            $report->update([
                'title' => $request->filled('title') ? $request->input('title') : $report->title,
                'content' => $request->filled('content') ? $request->input('content') : $report->content
            ]);


            return response()->json([
                'status' => 'success',
                'message' => 'Report updated successfully',
                'data' => new ReportResource($report)
            ], 200); // 200 OK

        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'errors' => $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Report not found',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while updating the report',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        try {
            // check if user has permission to delete  using ReportPolicy
            $this->authorize('delete', Report::class);

            $report->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Report deleted successfully',
            ], 200); // 200 OK
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'errors' => $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Report not found.',
            ], 404); // 404 Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error while deleting the report',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display all deleted reports.
     *
     * @return \Illuminate\Http\Response
     */
    public function trashed()
    {
        try {
            // check if user has permission to view deleted reports  using ReportPolicy
            $this->authorize('viewTrashed', Report::class);

            $deletedReports = ReportResource::collection(Report::onlyTrashed()->paginate(10));
            return response()->json([
                'status' => 'success',
                'data' => ReportResource::collection($deletedReports),
                'pagination' => [
                    'total' => $deletedReports->total(),
                    'per_page' => $deletedReports->perPage(),
                    'current_page' => $deletedReports->currentPage(),
                    'last_page' => $deletedReports->lastPage(),
                    'next_page_url' => $deletedReports->nextPageUrl(),
                    'prev_page_url' => $deletedReports->previousPageUrl(),
                ]
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'errors' => $e->getMessage()
            ], 403); // 403 Forbidden
        }
        // problem encountred while getting reports
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
     * @param  $report
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            // check if user has permission to view deleted reports  using ReportPolicy
            $this->authorize('restore', Report::class);

            $restoredReport = Report::withTrashed()->findOrFail($id);
            $restoredReport->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Report restored.',
                'data' => new ReportResource($restoredReport)
            ], 200); // 200 ok

        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'errors' => $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Report not found.',
            ], 404);
        }
        // problem encountred while getting reports
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
     * @param  $report
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        try {
            // check if user has permission to force deleted report using ReportPolicy
            $this->authorize('forceDelete', Report::class);
            $report = Report::onlyTrashed()->findOrFail($id);

            $report->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Report permanently deleted.',
            ], 200);
        }
        // user doesn't have permission
        catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'errors' => $e->getMessage()
            ], 403); // 403 Forbidden
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Report not found.',
            ], 404);
        }
        // problem encountred while getting reports
        catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
}
