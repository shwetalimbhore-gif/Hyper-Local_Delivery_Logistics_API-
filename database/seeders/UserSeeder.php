<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        $users = [
            // Admin User
            [
                'role_id' => 1, // Admin role
                'name' => 'Admin User',
                'email' => 'admin@hyperlocal.com',
                'password' => Hash::make('password123'),
                'phone' => '9876543210',
                'address' => 'Admin Office, Main Hub, Andheri East, Mumbai',
                'profile_image' => null,
                'is_active' => true,
                'last_login_at' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Rider 1
            [
                'role_id' => 2, // Rider role
                'name' => 'Rahul Sharma',
                'email' => 'rahul.rider@hyperlocal.com',
                'password' => Hash::make('password123'),
                'phone' => '9876543211',
                'address' => 'Shanti Nagar, Andheri East, Mumbai - 400069',
                'profile_image' => null,
                'is_active' => true,
                'last_login_at' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Rider 2
            [
                'role_id' => 2, // Rider role
                'name' => 'Priya Patel',
                'email' => 'priya.rider@hyperlocal.com',
                'password' => Hash::make('password123'),
                'phone' => '9876543212',
                'address' => 'Vile Parle West, Mumbai - 400056',
                'profile_image' => null,
                'is_active' => true,
                'last_login_at' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Rider 3
            [
                'role_id' => 2, // Rider role
                'name' => 'Amit Singh',
                'email' => 'amit.rider@hyperlocal.com',
                'password' => Hash::make('password123'),
                'phone' => '9876543213',
                'address' => 'Juhu, Mumbai - 400049',
                'profile_image' => null,
                'is_active' => true,
                'last_login_at' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
        $this->command->info('Users seeded successfully!');
        $this->command->info('  - 1 Admin');
        $this->command->info('  - 3 Riders');
    }
}
