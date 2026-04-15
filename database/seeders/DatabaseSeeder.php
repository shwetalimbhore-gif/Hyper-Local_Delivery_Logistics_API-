<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call all seeders in correct order
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ParcelStatusSeeder::class,
            HubSeeder::class,
            RiderSeeder::class,
            ParcelSeeder::class,
            ParcelStatusHistorySeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
