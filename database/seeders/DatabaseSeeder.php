<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            VillaSeeder::class,
            GuestSeeder::class,
            BookingSeeder::class,
            MaintenanceTaskSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
