<?php

namespace Database\Seeders;

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
        $users = [
            [
                'name' => 'Alexander Vance (General Manager)',
                'email' => 'admin@villamanagement.local',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN,
                'phone' => '+1 (555) 019-2831',
                'status' => 'active',
            ],
            [
                'name' => 'Elena Rostova (Operations Manager)',
                'email' => 'manager@villamanagement.local',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_MANAGER,
                'phone' => '+1 (555) 019-2832',
                'status' => 'active',
            ],
            [
                'name' => 'Marcus Chen (Head Concierge)',
                'email' => 'concierge@villamanagement.local',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_STAFF,
                'phone' => '+1 (555) 019-2833',
                'status' => 'active',
            ],
            [
                'name' => 'Aria Thorne (Housekeeping Lead)',
                'email' => 'housekeeping@villamanagement.local',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_STAFF,
                'phone' => '+1 (555) 019-2834',
                'status' => 'active',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(['email' => $userData['email']], $userData);
        }
    }
}
