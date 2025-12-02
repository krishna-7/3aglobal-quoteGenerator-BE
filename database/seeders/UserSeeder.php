<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Seed default users with different roles.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => 'password', // Will be hashed by setPasswordAttribute mutator
                'user_type_id' => '1',
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'password' => 'password', // Will be hashed by setPasswordAttribute mutator
                'user_type_id' => '2',
            ],
            [
                'name' => 'Client User',
                'email' => 'client@example.com',
                'password' => 'password', // Will be hashed by setPasswordAttribute mutator
                'user_type_id' => '3',
            ],
            [
                'name' => 'Operations User',
                'email' => 'operations@example.com',
                'password' => 'password', // Will be hashed by setPasswordAttribute mutator
                'user_type_id' => '3',
            ],
        ];
        
        // Use User::create() to trigger the setPasswordAttribute mutator
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
