<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ParcelSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('parcels')->truncate();
        Schema::enableForeignKeyConstraints();

        $parcels = [
            // Parcel 1: Delivered
            [
                'tracking_number' => 'HLD' . date('Ymd') . '001',
                'reference_number' => 'REF001',
                'sender_name' => 'Amit Patel',
                'sender_phone' => '9988776655',
                'sender_email' => 'amit@example.com',
                'sender_address' => '123, Gandhi Nagar, Andheri East, Mumbai',
                'receiver_name' => 'Priya Singh',
                'receiver_phone' => '8877665544',
                'receiver_email' => 'priya@example.com',
                'receiver_address' => '45, Lake View Apartments, Bandra West, Mumbai',
                'parcel_name' => 'Mobile Phone',
                'parcel_description' => 'Samsung Galaxy S23 - Fragile',
                'weight' => 0.50,
                'size' => 45.00,
                'parcel_type' => 'electronics',
                'delivery_charge' => 120.00,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'status_id' => 5, // delivered
                'source_hub_id' => 1,
                'assigned_rider_id' => 1,
                'assigned_at' => now()->subHours(5),
                'picked_up_at' => now()->subHours(4),
                'out_for_delivery_at' => now()->subHours(3),
                'delivered_at' => now()->subHours(1),
                'delivery_attempts' => 1,
                'failure_reason' => null,
                'notes' => 'Handle with care',
                'created_by' => 1, // Admin
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(1),
            ],
            // Parcel 2: In Transit
            [
                'tracking_number' => 'HLD' . date('Ymd') . '002',
                'reference_number' => 'REF002',
                'sender_name' => 'Neha Gupta',
                'sender_phone' => '9876543210',
                'sender_email' => 'neha@example.com',
                'sender_address' => '78, Green Park, Powai, Mumbai',
                'receiver_name' => 'Vikram Mehta',
                'receiver_phone' => '8765432109',
                'receiver_email' => 'vikram@example.com',
                'receiver_address' => '12, Silver Oak, Juhu, Mumbai',
                'parcel_name' => 'Laptop',
                'parcel_description' => 'Dell XPS 15 - High value item',
                'weight' => 2.50,
                'size' => 120.00,
                'parcel_type' => 'electronics',
                'delivery_charge' => 200.00,
                'payment_method' => 'online',
                'payment_status' => 'paid',
                'status_id' => 4, // out_for_delivery
                'source_hub_id' => 1,
                'assigned_rider_id' => 1,
                'assigned_at' => now()->subHours(3),
                'picked_up_at' => now()->subHours(2),
                'out_for_delivery_at' => now()->subHours(1),
                'delivered_at' => null,
                'delivery_attempts' => 0,
                'failure_reason' => null,
                'notes' => 'Signature required',
                'created_by' => 1,
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(1),
            ],
            // Parcel 3: Pending Assignment
            [
                'tracking_number' => 'HLD' . date('Ymd') . '003',
                'reference_number' => 'REF003',
                'sender_name' => 'Rohan Desai',
                'sender_phone' => '7654321098',
                'sender_email' => 'rohan@example.com',
                'sender_address' => '56, Shivaji Nagar, Andheri West, Mumbai',
                'receiver_name' => 'Sneha Patil',
                'receiver_phone' => '6543210987',
                'receiver_email' => 'sneha@example.com',
                'receiver_address' => '34, Rose Garden, Bandra East, Mumbai',
                'parcel_name' => 'Clothes',
                'parcel_description' => 'Traditional wear - 3 sets',
                'weight' => 1.00,
                'size' => 80.00,
                'parcel_type' => 'package',
                'delivery_charge' => 80.00,
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'status_id' => 1, // pending
                'source_hub_id' => 2,
                'assigned_rider_id' => null,
                'assigned_at' => null,
                'picked_up_at' => null,
                'out_for_delivery_at' => null,
                'delivered_at' => null,
                'delivery_attempts' => 0,
                'failure_reason' => null,
                'notes' => null,
                'created_by' => 1,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            // Parcel 4: Failed Delivery
            [
                'tracking_number' => 'HLD' . date('Ymd') . '004',
                'reference_number' => 'REF004',
                'sender_name' => 'Kiran Joshi',
                'sender_phone' => '5432109876',
                'sender_email' => 'kiran@example.com',
                'sender_address' => '90, Lake City, Powai, Mumbai',
                'receiver_name' => 'Anil Kapoor',
                'receiver_phone' => '4321098765',
                'receiver_email' => 'anil@example.com',
                'receiver_address' => '67, Hill View, Juhu, Mumbai',
                'parcel_name' => 'Books',
                'parcel_description' => 'Educational books - 5kg set',
                'weight' => 5.00,
                'size' => 150.00,
                'parcel_type' => 'package',
                'delivery_charge' => 150.00,
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'status_id' => 6, // failed_delivery
                'source_hub_id' => 3,
                'assigned_rider_id' => 2,
                'assigned_at' => now()->subDays(1),
                'picked_up_at' => now()->subDays(1)->addHours(1),
                'out_for_delivery_at' => now()->subDays(1)->addHours(2),
                'delivered_at' => null,
                'delivery_attempts' => 2,
                'failure_reason' => 'Receiver Not Available',
                'notes' => 'Customer not picking up phone',
                'created_by' => 1,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(1),
            ],
        ];

        DB::table('parcels')->insert($parcels);
        $this->command->info('Parcels seeded successfully!');
        $this->command->info('  - 4 Parcels created');
    }
}
