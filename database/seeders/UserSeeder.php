<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a user
        $user = User::create([
            'username' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('azerty123')
        ]);

        // Assign the admin role to the user
        $adminRole = Role::where('name', 'admin')->first();
        $user->roles()->attach($adminRole);


        // Create a user
        $user2 = User::create([
            'username' => 'guest',
            'email' => 'guest@gmail.com',
            'password' => Hash::make('azerty123')
        ]);

        // Assign the guest role to the user
        $guestRole = Role::where('name', 'guest')->first();
        $user2->roles()->attach($guestRole);
    }
}
