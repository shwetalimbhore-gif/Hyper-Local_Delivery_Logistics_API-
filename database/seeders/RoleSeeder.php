<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();

        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full system access - Create parcels, manage riders, view all deliveries',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rider',
                'slug' => 'rider',
                'description' => 'View assigned parcels and update delivery status only',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
        $this->command->info('Roles seeded successfully!');
        $this->command->info('  - Admin');
        $this->command->info('  - Rider');
    }
}
