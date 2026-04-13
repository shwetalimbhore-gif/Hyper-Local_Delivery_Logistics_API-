<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Method A: Using DB facade
        DB::table('roles')->insert([
            ['name' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'User', 'created_at' => now(), 'updated_at' => now()],

        ]);

        // OR Method B: Using Eloquent model (cleaner)
        // Role::create(['name' => 'Admin']);
        // Role::create(['name' => 'User']);

    }
}
