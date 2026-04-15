<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ParcelStatusSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('parcel_statuses')->truncate();
        Schema::enableForeignKeyConstraints();

        $statuses = [
            [
                'id' => 1,
                'name' => 'pending',
                'slug' => 'pending',
                'display_name' => 'Pending',
                'color_code' => '#ffc107',
                'is_rider_updatable' => false,
                'sequence_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'assigned',
                'slug' => 'assigned',
                'display_name' => 'Assigned to Rider',
                'color_code' => '#17a2b8',
                'is_rider_updatable' => false,
                'sequence_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'picked_up',
                'slug' => 'picked-up',
                'display_name' => 'Picked Up',
                'color_code' => '#007bff',
                'is_rider_updatable' => true,
                'sequence_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'out_for_delivery',
                'slug' => 'out-for-delivery',
                'display_name' => 'Out for Delivery',
                'color_code' => '#fd7e14',
                'is_rider_updatable' => true,
                'sequence_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'delivered',
                'slug' => 'delivered',
                'display_name' => 'Delivered',
                'color_code' => '#28a745',
                'is_rider_updatable' => true,
                'sequence_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'failed_delivery',
                'slug' => 'failed-delivery',
                'display_name' => 'Delivery Failed',
                'color_code' => '#dc3545',
                'is_rider_updatable' => true,
                'sequence_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'returned_to_hub',
                'slug' => 'returned-to-hub',
                'display_name' => 'Returned to Hub',
                'color_code' => '#6c757d',
                'is_rider_updatable' => true,
                'sequence_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'returned_to_sender',
                'slug' => 'returned-to-sender',
                'display_name' => 'Returned to Sender',
                'color_code' => '#6c757d',
                'is_rider_updatable' => false,
                'sequence_order' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'cancelled',
                'slug' => 'cancelled',
                'display_name' => 'Cancelled',
                'color_code' => '#dc3545',
                'is_rider_updatable' => false,
                'sequence_order' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('parcel_statuses')->insert($statuses);
        $this->command->info('Parcel Statuses seeded successfully!');
        $this->command->info('  - 9 Statuses created');
    }
}
