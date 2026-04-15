<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('payments')->truncate();
        Schema::enableForeignKeyConstraints();

        $payments = [
            [
                'parcel_id' => 1,
                'amount' => 120.00,
                'payment_method' => 'cash',
                'transaction_id' => null,
                'payment_status' => 'completed',
                'collected_by' => 2, // Rider collected
                'collected_at' => now()->subHours(1),
                'notes' => 'Cash collected on delivery',
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
            [
                'parcel_id' => 2,
                'amount' => 200.00,
                'payment_method' => 'online',
                'transaction_id' => 'TXN123456789',
                'payment_status' => 'completed',
                'collected_by' => null,
                'collected_at' => null,
                'notes' => 'Paid online via Razorpay',
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(4),
            ],
        ];

        DB::table('payments')->insert($payments);
        $this->command->info('Payments seeded successfully!');
    }
}
