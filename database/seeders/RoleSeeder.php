<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'System Administrator',
                'description' => 'Full system access and management',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'chairman',
                'display_name' => 'Society Chairman',
                'description' => 'Manages entire society operations',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'block_chairman',
                'display_name' => 'Block Chairman',
                'description' => 'Manages specific block operations',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'resident',
                'display_name' => 'Resident',
                'description' => 'Regular society member',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'tenant',
                'display_name' => 'Tenant',
                'description' => 'Temporary resident (rented flat)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}