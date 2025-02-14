<?php

use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\ClientPortalController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ClientServiceController;
use App\Http\Controllers\Api\V1\InteractionController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentReminderController;
use App\Http\Controllers\Api\V1\PaymentScheduleController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ScheduleController;
use App\Http\Controllers\Api\V1\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// api/v1
Route::group(['prefix' => 'v1'], function () {
    // to register new users as guests
    Route::post('register', [AuthController::class, 'register']);
    // to logging in  users
    Route::post('login', [AuthController::class, 'login']);
    // route to referesh the access token
    Route::post('refresh', [AuthController::class, 'refresh']);

    // authentication middleware for users
    Route::middleware('auth.jwt')->group(function () {
        // route to get user id and role
        Route::get('me', [AuthController::class, 'me']);
        // logout route
        Route::post('logout', [AuthController::class, 'logout']);

        /* ****************     User API      ****************    */

        // 1.	User Management:
        Route::prefix('users')->group(function () {
            // restore deleted user
            Route::patch('/{user}/restore', [UserController::class, 'restore']);
            // force delete a deleted user
            Route::delete('/{user}/force-delete', [UserController::class, 'forceDelete']);
            // view deleted users
            Route::get('/trashed', [UserController::class, 'trashed']);
        });
        // view all users, view single user, create, edit, soft delete a user
        Route::apiResource('users', UserController::class);
        // 2.	Role and Permission Management:
        Route::get('permissions/', [RoleController::class, 'permissions']);
        Route::prefix('roles')->group(function () {
            Route::get('/trashed', [RoleController::class, 'trashed']);
            Route::patch('/{role}/restore', [RoleController::class, 'restore']);
            Route::delete('/{role}/force-delete', [RoleController::class, 'forceDelete']);
        });
        Route::apiResource('roles', RoleController::class);




        /* ****************     Client API      ****************    */

        // 2.	Client Search and Filtering
        Route::get('clients/search', [ClientController::class, 'search']);
        Route::get('clients/check-duplicates', [ClientController::class, 'getDuplicates']);
        // 1.	Client Management
        Route::prefix('clients')->group(function () {
            Route::get('/trashed', [ClientController::class, 'trashed']);
            // restore deleted client
            Route::patch('/{client}/restore', [ClientController::class, 'restore']);
            Route::delete('/{client}/force-delete', [ClientController::class, 'forceDelete']);
        });
        Route::apiResource('clients', ClientController::class);

        /* ****************     Payment  API      ****************    */

        // 2.	Payment Plans and Schedules:
        Route::get('payments/schedules/{client}', [PaymentScheduleController::class, 'index']);
        Route::post('payments/schedules/{client}', [PaymentScheduleController::class, 'store']);
        Route::put('payments/schedules/{client}/{paymentSchedule}', [PaymentScheduleController::class, 'update']);
        // 3.	Payment Reminders:
        Route::post('payments/reminder/{client}', [PaymentReminderController::class, 'reminder']);
        Route::get('payments/reminder/history/{client}', [PaymentReminderController::class, 'history']);
        // 1.	Payment Management:
        Route::prefix('payments')->group(function () {
            Route::get('/trashed', [PaymentController::class, 'trashed']);
            // restore deleted payment
            Route::patch('/{payment}/restore', [PaymentController::class, 'restore']);
            Route::delete('/{payment}/force-delete', [PaymentController::class, 'forceDelete']);
        });
        Route::apiResource('payments', PaymentController::class);

        /* ****************     Interaction API      ****************    */

        // 2.	Interaction Analytics:
        Route::get('interactions/summary', [InteractionController::class, 'getSummary']);
        Route::get('interactions/upcomming', [InteractionController::class, 'getUpcomming']);
        // 1.	Interaction Logging:
        Route::prefix('interactions')->group(function () {
            Route::get('/trashed', [InteractionController::class, 'trashed']);
            Route::patch('/{interaction}/restore', [InteractionController::class, 'restore']);
            Route::delete('/{interaction}/force-delete', [InteractionController::class, 'forceDelete']);
        });
        Route::apiResource('interactions', InteractionController::class);


        /* ****************     services API      ****************    */

        // 2.	Client Service Preferences:
        Route::get('clients/{client}/services', [ClientServiceController::class, 'getServices']);
        Route::post('clients/{client}/services', [ClientServiceController::class, 'enrollService']);
        Route::delete('clients/{client}/services/{service}', [ClientServiceController::class, 'removeService']);
        Route::delete('clients/{client}/services/{service}/force-delete', [ClientServiceController::class, 'forceRemoveService']);
        // 1.	Service Management:
        Route::prefix('services')->group(function () {
            Route::get('/search', [ServiceController::class, 'search']);
            Route::get('/trashed', [ServiceController::class, 'trashed']);
            Route::patch('/{service}/restore', [ServiceController::class, 'restore']);
            Route::delete('/{service}/force-delete', [ServiceController::class, 'forceDelete']);
        });
        Route::apiResource('services', ServiceController::class);


        /* ****************     Schedules API      ****************    */

        // 2.	Conflict Detection and Resolution:
        Route::post('schedules/conflict-check', [ScheduleController::class, 'conflictCheck']);
        Route::get('schedules/conflict-resolutions', [ScheduleController::class, 'conflictResolutions']);
        // 1.	Course and Appointment Scheduling:
        Route::prefix('schedules')->group(function () {
            Route::get('/trashed', [ScheduleController::class, 'trashed']);
            Route::patch('/{schedule}/restore', [ScheduleController::class, 'restore']);
            Route::delete('/{schedule}/force-delete', [ScheduleController::class, 'forceDelete']);
        });
        Route::apiResource('schedules', ScheduleController::class);


        /* ****************     Notification API      ****************    */

        // 2.	Automated Notifications:
        // Route::post('notifications/automated', [NotificationController::class,'autoNotification']);
        // Route::get('notifications/automhistoryated/', [NotificationController::class,'notificationHistory']);
        // 1.	Notification Management:
        Route::prefix('notifications')->group(function () {
            Route::get('/trashed', [NotificationController::class, 'trashed']);
            Route::patch('/{notification}/restore', [NotificationController::class, 'restore']);
            Route::delete('/{notification}/force-delete', [NotificationController::class, 'forceDelete']);
        });
        Route::apiResource('notifications', NotificationController::class);


        /* ****************     Reporting API      ****************    */

        // 2.	Custom Reports:
        // Route::post('reports/custom', ReportController::class);
        // Route::get('reports/custom/{report}', ReportController::class);
        // 1.	Report Generation:
        Route::prefix('reports')->group(function () {
            Route::get('/trashed', [ReportController::class, 'trashed']);
            Route::patch('/{report}/restore', [ReportController::class, 'restore']);
            Route::delete('/{report}/force-delete', [ReportController::class, 'forceDelete']);
        });
        Route::apiResource('reports', ReportController::class);
    });

    /* ****************     Client-Portal API      ****************    */

    // 2.	Client Portal API:
    Route::prefix('client-portal')->group(function () {
        Route::post('/login', [ClientPortalController::class, 'login']);
        // middleware to checkauthentication for clients
        Route::middleware('auth.jwt-client-api')->group(function () {
            Route::get('/', [ClientPortalController::class, 'getProfile']);
            Route::get('/profile', [ClientPortalController::class, 'getProfile']);
            Route::put('/profile', [ClientPortalController::class, 'editProfile']);
            Route::get('/schedules', [ClientPortalController::class, 'schedule']);
            Route::get('/payments', [ClientPortalController::class, 'payments']);
            // Route::get('/feedback', [ClientPortalController::class, 'feedback']);
            Route::post('/logout', [ClientPortalController::class, 'logout']);
        });
    });
    /* ****************     Analytics API      ****************    */

    // 2.	3.	Analytics  API:
    Route::prefix('analytics')->group(function () {
        // middleware to checkauthentication for users
        Route::middleware('auth.jwt')->group(function () {
            Route::get('/', [AnalyticsController::class, 'summary']);
            Route::get('/summary', [AnalyticsController::class, 'summary']);
            Route::put('/service-performance', [AnalyticsController::class, 'servicePerformance']);
            Route::put('/client-engagement', [AnalyticsController::class, 'clientEngagement']);
        });
    });
});
