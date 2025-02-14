<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User permissions
        Permission::create(['name' => 'viewAny user', 'context' => 'user']);
        Permission::create(['name' => 'viewTrashed user', 'context' => 'user']);
        Permission::create(['name' => 'view user', 'context' => 'user']);
        Permission::create(['name' => 'create user', 'context' => 'user']);
        Permission::create(['name' => 'update user', 'context' => 'user']);
        Permission::create(['name' => 'delete user', 'context' => 'user']);
        Permission::create(['name' => 'restore user', 'context' => 'user']);
        Permission::create(['name' => 'forceDelete user', 'context' => 'user']);

        // Role permissions
        Permission::create(['name' => 'viewAny role', 'context' => 'role']);
        Permission::create(['name' => 'viewTrashed role', 'context' => 'role']);
        Permission::create(['name' => 'view role', 'context' => 'role']);
        Permission::create(['name' => 'create role', 'context' => 'role']);
        Permission::create(['name' => 'update role', 'context' => 'role']);
        Permission::create(['name' => 'delete role', 'context' => 'role']);
        Permission::create(['name' => 'restore role', 'context' => 'role']);
        Permission::create(['name' => 'forceDelete role', 'context' => 'role']);

        // Client permissions
        Permission::create(['name' => 'viewAny client', 'context' => 'client']);
        Permission::create(['name' => 'viewTrashed client', 'context' => 'client']);
        Permission::create(['name' => 'view client', 'context' => 'client']);
        Permission::create(['name' => 'create client', 'context' => 'client']);
        Permission::create(['name' => 'update client', 'context' => 'client']);
        Permission::create(['name' => 'delete client', 'context' => 'client']);
        Permission::create(['name' => 'restore client', 'context' => 'client']);
        Permission::create(['name' => 'forceDelete client', 'context' => 'client']);
        Permission::create(['name' => 'enroll client', 'context' => 'client']);
        Permission::create(['name' => 'get enrolled client', 'context' => 'client']);
        Permission::create(['name' => 'remove enrolled client', 'context' => 'client']);
        Permission::create(['name' => 'force remove enrolled client', 'context' => 'client']);

        // Interaction permissions
        Permission::create(['name' => 'viewAny interaction', 'context' => 'interaction']);
        Permission::create(['name' => 'viewTrashed interaction', 'context' => 'interaction']);
        Permission::create(['name' => 'view interaction', 'context' => 'interaction']);
        Permission::create(['name' => 'create interaction', 'context' => 'interaction']);
        Permission::create(['name' => 'update interaction', 'context' => 'interaction']);
        Permission::create(['name' => 'delete interaction', 'context' => 'interaction']);
        Permission::create(['name' => 'restore interaction', 'context' => 'interaction']);
        Permission::create(['name' => 'forceDelete interaction', 'context' => 'interaction']);
        Permission::create(['name' => 'forceDelete interaction', 'context' => 'interaction']);
        Permission::create(['name' => 'summary interaction', 'context' => 'interaction']);
        Permission::create(['name' => 'upcomming interaction', 'context' => 'interaction']);

        // Notification permissions
        Permission::create(['name' => 'viewAny notification', 'context' => 'notification']);
        Permission::create(['name' => 'viewTrashed notification', 'context' => 'notification']);
        Permission::create(['name' => 'view notification', 'context' => 'notification']);
        Permission::create(['name' => 'create notification', 'context' => 'notification']);
        Permission::create(['name' => 'update notification', 'context' => 'notification']);
        Permission::create(['name' => 'delete notification', 'context' => 'notification']);
        Permission::create(['name' => 'restore notification', 'context' => 'notification']);
        Permission::create(['name' => 'forceDelete notification', 'context' => 'notification']);

        // Payment permissions
        Permission::create(['name' => 'viewAny payment', 'context' => 'payment']);
        Permission::create(['name' => 'viewTrashed payment', 'context' => 'payment']);
        Permission::create(['name' => 'view payment', 'context' => 'payment']);
        Permission::create(['name' => 'create payment', 'context' => 'payment']);
        Permission::create(['name' => 'update payment', 'context' => 'payment']);
        Permission::create(['name' => 'delete payment', 'context' => 'payment']);
        Permission::create(['name' => 'restore payment', 'context' => 'payment']);
        Permission::create(['name' => 'forceDelete payment', 'context' => 'payment']);
        Permission::create(['name' => 'create paymentReminder', 'context' => 'payment']);
        Permission::create(['name' => 'view paymentReminder', 'context' => 'payment']);



        // Payment Schedule permissions
        Permission::create(['name' => 'viewAny paymentSchedule', 'context' => 'paymentSchedule']);
        Permission::create(['name' => 'viewTrashed paymentSchedule', 'context' => 'paymentSchedule']);
        Permission::create(['name' => 'view paymentSchedule', 'context' => 'paymentSchedule']);
        Permission::create(['name' => 'create paymentSchedule', 'context' => 'paymentSchedule']);
        Permission::create(['name' => 'update paymentSchedule', 'context' => 'paymentSchedule']);
        Permission::create(['name' => 'delete paymentSchedule', 'context' => 'paymentSchedule']);
        Permission::create(['name' => 'restore paymentSchedule', 'context' => 'paymentSchedule']);
        Permission::create(['name' => 'forceDelete paymentSchedule', 'context' => 'paymentSchedule']);

        // Report permissions
        Permission::create(['name' => 'viewAny report', 'context' => 'report']);
        Permission::create(['name' => 'viewTrashed report', 'context' => 'report']);
        Permission::create(['name' => 'view report', 'context' => 'report']);
        Permission::create(['name' => 'create report', 'context' => 'report']);
        Permission::create(['name' => 'update report', 'context' => 'report']);
        Permission::create(['name' => 'delete report', 'context' => 'report']);
        Permission::create(['name' => 'restore report', 'context' => 'report']);
        Permission::create(['name' => 'forceDelete report', 'context' => 'report']);

        // Schedule permissions
        Permission::create(['name' => 'viewAny schedule', 'context' => 'schedule']);
        Permission::create(['name' => 'viewTrashed schedule', 'context' => 'schedule']);
        Permission::create(['name' => 'view schedule', 'context' => 'schedule']);
        Permission::create(['name' => 'create schedule', 'context' => 'schedule']);
        Permission::create(['name' => 'update schedule', 'context' => 'schedule']);
        Permission::create(['name' => 'delete schedule', 'context' => 'schedule']);
        Permission::create(['name' => 'restore schedule', 'context' => 'schedule']);
        Permission::create(['name' => 'forceDelete schedule', 'context' => 'schedule']);

        // Service permissions
        Permission::create(['name' => 'viewAny service', 'context' => 'service']);
        Permission::create(['name' => 'viewTrashed service', 'context' => 'service']);
        Permission::create(['name' => 'view service', 'context' => 'service']);
        Permission::create(['name' => 'create service', 'context' => 'service']);
        Permission::create(['name' => 'update service', 'context' => 'service']);
        Permission::create(['name' => 'delete service', 'context' => 'service']);
        Permission::create(['name' => 'restore service', 'context' => 'service']);
        Permission::create(['name' => 'forceDelete service', 'context' => 'service']);

        // User permissions (repeated for completeness)
        Permission::create(['name' => 'viewAny user', 'context' => 'user']);
        Permission::create(['name' => 'viewTrashed user', 'context' => 'user']);
        Permission::create(['name' => 'view user', 'context' => 'user']);
        Permission::create(['name' => 'create user', 'context' => 'user']);
        Permission::create(['name' => 'update user', 'context' => 'user']);
        Permission::create(['name' => 'delete user', 'context' => 'user']);
        Permission::create(['name' => 'restore user', 'context' => 'user']);
        Permission::create(['name' => 'forceDelete user', 'context' => 'user']);
    }
}
