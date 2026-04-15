<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RiderSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('riders')->truncate();
        Schema::enableForeignKeyConstraints();

        $riders = [
            [
                'user_id' => 2, // Rahul Sharma (User ID 2)
                'hub_id' => 1, // Andheri Hub
                'employee_id' => 'RID001',
                'vehicle_type' => 'bike',
                'vehicle_number' => 'MH02AB1234',
                'vehicle_model' => 'Honda Shine',
                'license_number' => 'MH012023456789',
                'max_weight_capacity' => 50.00,
                'max_size_capacity' => 100.00,
                'status' => 'available',
                'total_deliveries' => 150,
                'successful_deliveries' => 145,
                'failed_deliveries' => 5,
                'rating' => 4.8,
                'earnings' => 15000.00,
                'is_verified' => true,
                'joined_date' => '2024-01-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3, // Priya Patel (User ID 3)
                'hub_id' => 2, // Bandra Hub
                'employee_id' => 'RID002',
                'vehicle_type' => 'scooter',
                'vehicle_number' => 'MH03CD5678',
                'vehicle_model' => 'Activa 6G',
                'license_number' => 'MH022023567890',
                'max_weight_capacity' => 40.00,
                'max_size_capacity' => 80.00,
                'status' => 'available',
                'total_deliveries' => 120,
                'successful_deliveries' => 118,
                'failed_deliveries' => 2,
                'rating' => 4.9,
                'earnings' => 12000.00,
                'is_verified' => true,
                'joined_date' => '2024-02-20',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4, // Amit Singh (User ID 4)
                'hub_id' => 1, // Andheri Hub
                'employee_id' => 'RID003',
                'vehicle_type' => 'bike',
                'vehicle_number' => 'MH04EF9012',
                'vehicle_model' => 'Bajaj Pulsar',
                'license_number' => 'MH032023678901',
                'max_weight_capacity' => 60.00,
                'max_size_capacity' => 120.00,
                'status' => 'busy',
                'total_deliveries' => 200,
                'successful_deliveries' => 195,
                'failed_deliveries' => 5,
                'rating' => 4.7,
                'earnings' => 20000.00,
                'is_verified' => true,
                'joined_date' => '2024-01-10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('riders')->insert($riders);
        $this->command->info('Riders seeded successfully!');
        $this->command->info('  - 3 Rider profiles created');
    }
}
