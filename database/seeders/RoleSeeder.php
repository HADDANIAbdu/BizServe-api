<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create([
            'name' => 'admin',
        ]);

        $guestRole = Role::create([
            'name' => 'guest',
        ]);

        $clientManagerRole = Role::create([
            'name' => 'client_manager',
        ]);

        $permissionIds = Permission::all()->pluck('id')->toArray();

        // Assign all permissions to the superadmin role
        $adminRole->permissions()->sync($permissionIds);
        $this->attachPermissions($clientManagerRole, 'client');
    }

    /**
     * Attach permissions to a role based on the context.
     *
     * @param Role $role
     * @param string $context
     * @return void
     */
    protected function attachPermissions(Role $role, string $context): void
    {
        $permissionIds = Permission::where('context', $context)
            ->pluck('id')
            ->toArray();

        $role->permissions()->sync($permissionIds);
    }
}
