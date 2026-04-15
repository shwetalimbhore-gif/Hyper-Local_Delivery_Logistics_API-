<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ParcelStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('parcel_status_histories')->truncate();
        Schema::enableForeignKeyConstraints();

        $histories = [
            // History for Parcel 1 (Delivered)
            [
                'parcel_id' => 1,
                'status_id' => 1, // pending
                'from_status_id' => null,
                'notes' => 'Parcel created and pending assignment',
                'updated_by' => 1,
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
            [
                'parcel_id' => 1,
                'status_id' => 2, // assigned
                'from_status_id' => 1,
                'notes' => 'Assigned to rider Rahul Sharma',
                'updated_by' => 1,
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ],
            [
                'parcel_id' => 1,
                'status_id' => 3, // picked_up
                'from_status_id' => 2,
                'notes' => 'Picked up from Andheri Hub',
                'updated_by' => 2, // Rider
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(4),
            ],
            [
                'parcel_id' => 1,
                'status_id' => 4, // out_for_delivery
                'from_status_id' => 3,
                'notes' => 'Out for delivery to Bandra West',
                'updated_by' => 2,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
            [
                'parcel_id' => 1,
                'status_id' => 5, // delivered
                'from_status_id' => 4,
                'notes' => 'Successfully delivered to Priya Singh',
                'updated_by' => 2,
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
            // History for Parcel 2 (In Transit)
            [
                'parcel_id' => 2,
                'status_id' => 1,
                'from_status_id' => null,
                'notes' => 'Parcel created',
                'updated_by' => 1,
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(4),
            ],
            [
                'parcel_id' => 2,
                'status_id' => 2,
                'from_status_id' => 1,
                'notes' => 'Assigned to rider Rahul Sharma',
                'updated_by' => 1,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
            [
                'parcel_id' => 2,
                'status_id' => 3,
                'from_status_id' => 2,
                'notes' => 'Picked up from hub',
                'updated_by' => 2,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
        ];

        DB::table('parcel_status_histories')->insert($histories);
        $this->command->info('Parcel Status Histories seeded successfully!');
    }
}
