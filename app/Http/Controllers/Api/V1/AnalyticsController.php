<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Interaction;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Provide a summary of client interactions.
     *
     * @return \Illuminate\Http\Response
     */
    public function summary()
    {
        try {
            // Get the summary of clients interactions
            $clientsSummary = Interaction::select('client_id', DB::raw('COUNT(*) as interactions_count'))
                ->groupBy('client_id')
                ->with('client') // Assuming the 'client' relationship is defined on the Interaction model
                ->get();

            // Get the summary of services per client
            $servicesSummary = Service::select('client_id', DB::raw('COUNT(*) as services_count'))
                ->groupBy('client_id')
                ->with('client') // Assuming the 'client' relationship is defined on the Service model
                ->get();

            // Get the total amount of payment per client
            $paymentSummary = Payment::select('client_id', DB::raw('SUM(amount) as total_payments'))
                ->groupBy('client_id')
                ->with('client') // Assuming the 'client' relationship is defined on the Payment model
                ->get();

            // Get the count of users
            $userstotal = User::select(DB::raw('COUNT(*) as users_count'))
                ->get();
            // Get the count of services
            $servicestotal = Service::select(DB::raw('COUNT(*) as services_count'))
                ->get();
            // Get the count of Clients
            $clientstotal = Client::select(DB::raw('COUNT(*) as clients_count'))
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Summary retrieved successfully',
                'data' => [
                    'clientsSummary' => $clientsSummary,
                    'servicesSummary' => $servicesSummary,
                    'paymentSummary' => $paymentSummary,
                    'userstotal' => $userstotal,
                    'servicestotal' => $servicestotal,
                    'clientstotal' => $clientstotal,
                ]
            ], 200); // 200 OK

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the summary',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }


    /**
     * Analyze and display service performance.
     *
     * @return \Illuminate\Http\Response
     */
    public function servicePerformance()
    {
        try {
            // Example: Performance by service
            $servicePerformance = Interaction::select('service_id', DB::raw('COUNT(*) as interactions_count'))
                ->groupBy('service_id')
                ->with('service') // Assuming a relationship is defined
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Service performance retrieved successfully',
                'data' => $servicePerformance
            ], 200); // 200 OK

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the service performance',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Analyze and display client engagement metrics.
     *
     * @return \Illuminate\Http\Response
     */
    public function clientEngagement()
    {
        try {
            // Example: Engagement metrics
            $clientEngagement = Client::select('id', 'name', DB::raw('COUNT(interactions.id) as interactions_count'))
                ->leftJoin('interactions', 'clients.id', '=', 'interactions.client_id')
                ->groupBy('clients.id', 'clients.name')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Client engagement metrics retrieved successfully',
                'data' => $clientEngagement
            ], 200); // 200 OK

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving client engagement metrics',
                'error' => $th->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
}
