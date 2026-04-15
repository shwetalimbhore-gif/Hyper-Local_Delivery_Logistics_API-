<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HubSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('hubs')->truncate();
        Schema::enableForeignKeyConstraints();

        $hubs = [
            [
                'name' => 'Andheri Main Hub',
                'code' => 'HUB001',
                'address' => 'Andheri East, Near Airport Road, Mumbai - 400069',
                'phone' => '022-1234567',
                'email' => 'andheri@hyperlocal.com',
                'manager_name' => 'Rajesh Kumar',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bandra Hub',
                'code' => 'HUB002',
                'address' => 'Bandra West, Near Linking Road, Mumbai - 400050',
                'phone' => '022-1234568',
                'email' => 'bandra@hyperlocal.com',
                'manager_name' => 'Sneha Patil',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Powai Hub',
                'code' => 'HUB003',
                'address' => 'Powai, Near IIT Bombay, Mumbai - 400076',
                'phone' => '022-1234569',
                'email' => 'powai@hyperlocal.com',
                'manager_name' => 'Vikram Mehta',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('hubs')->insert($hubs);
        $this->command->info('Hubs seeded successfully!');
        $this->command->info('  - 3 Hubs created');
    }
}
