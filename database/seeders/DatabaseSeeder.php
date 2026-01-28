<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Block;
use App\Models\Flat;
use App\Models\Notice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First, seed roles
        $this->call(RoleSeeder::class);

        // Create Blocks
        $blockA = Block::create([
            'name' => 'Block A',
            'block_number' => 'BLOCK-A',
            'total_floors' => 5,
            'flats_per_floor' => 4,
            'description' => 'Main block with garden view'
        ]);

        $blockB = Block::create([
            'name' => 'Block B',
            'block_number' => 'BLOCK-B',
            'total_floors' => 5,
            'flats_per_floor' => 4,
            'description' => 'Block with pool view'
        ]);

        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@societyapp.com',
            'password' => Hash::make('password'),
            'role_id' => 1, // admin
            'phone' => '9876543210',
            'is_active' => true,
        ]);

        // Create Society Chairman
        $chairman = User::create([
            'name' => 'Rajesh Kumar',
            'email' => 'chairman@societyapp.com',
            'password' => Hash::make('password'),
            'role_id' => 2, // chairman
            'phone' => '9876543211',
            'is_active' => true,
        ]);

        // Create some flats in Block A
        for ($floor = 1; $floor <= 5; $floor++) {
            for ($flat = 1; $flat <= 4; $flat++) {
                $flatNumber = $floor . '0' . $flat;
                Flat::create([
                    'block_id' => $blockA->id,
                    'flat_number' => $flatNumber,
                    'full_number' => 'A-' . $flatNumber,
                    'floor' => $floor,
                    'carpet_area' => rand(800, 1200),
                    'bedrooms' => rand(2, 3),
                    'status' => 'occupied',
                ]);
            }
        }

        // Create some flats in Block B
        for ($floor = 1; $floor <= 5; $floor++) {
            for ($flat = 1; $flat <= 4; $flat++) {
                $flatNumber = $floor . '0' . $flat;
                Flat::create([
                    'block_id' => $blockB->id,
                    'flat_number' => $flatNumber,
                    'full_number' => 'B-' . $flatNumber,
                    'floor' => $floor,
                    'carpet_area' => rand(900, 1300),
                    'bedrooms' => rand(2, 4),
                    'status' => rand(0, 10) > 8 ? 'vacant' : 'occupied',
                ]);
            }
        }

        // Assign chairman to a flat
        $chairmanFlat = Flat::where('full_number', 'A-501')->first();
        $chairmanFlat->update(['owner_id' => $chairman->id]);
        $chairman->update(['flat_id' => $chairmanFlat->id]);

        // Create Block Chairman for Block A
        $blockChairmanA = User::create([
            'name' => 'Amit Sharma',
            'email' => 'blockchairman.a@societyapp.com',
            'password' => Hash::make('password'),
            'role_id' => 3, // block_chairman
            'phone' => '9876543212',
            'is_active' => true,
        ]);

        $flatA301 = Flat::where('full_number', 'A-301')->first();
        $flatA301->update(['owner_id' => $blockChairmanA->id]);
        $blockChairmanA->update(['flat_id' => $flatA301->id]);
        $blockA->update(['chairman_id' => $blockChairmanA->id]);

        // Create some residents
        $residentNames = [
            'Pranjal Gupte',
            'Sneha Patil',
            'Rahul Deshmukh',
            'Priya Mehta',
            'Vikram Singh'
        ];

        foreach ($residentNames as $index => $name) {
            $flatNumber = 'A-' . (10 + $index + 1);
            $flat = Flat::where('full_number', $flatNumber)->first();
            
            if ($flat) {
                $resident = User::create([
                    'name' => $name,
                    'email' => strtolower(str_replace(' ', '.', $name)) . '@gmail.com',
                    'password' => Hash::make('password'),
                    'role_id' => 4, // resident
                    'flat_id' => $flat->id,
                    'phone' => '98765432' . (20 + $index),
                    'resident_type' => 'owner',
                    'is_active' => true,
                ]);
                
                $flat->update(['owner_id' => $resident->id]);
            }
        }

        // Create some notices
        Notice::create([
            'title' => 'Water Supply Interruption - January 25',
            'content' => 'Dear Residents, The water supply will be interrupted on January 25th from 10 AM to 2 PM due to maintenance work on the main pipeline. Please store water accordingly. We apologize for any inconvenience caused.',
            'priority' => 'high',
            'status' => 'published',
            'author_id' => $chairman->id,
            'valid_from' => now(),
            'valid_until' => now()->addDays(2),
            'published_at' => now(),
        ]);

        Notice::create([
            'title' => 'Annual General Meeting - February 15',
            'content' => 'The Annual General Meeting of Sunrise Apartments will be held on February 15, 2026 at 6:00 PM in the community hall. All residents are requested to attend. Agenda: Budget approval, upcoming projects, and election of new committee members.',
            'priority' => 'medium',
            'status' => 'published',
            'author_id' => $chairman->id,
            'valid_from' => now(),
            'valid_until' => now()->addDays(21),
            'published_at' => now()->subDays(2),
        ]);

        Notice::create([
            'title' => 'Updated Parking Guidelines',
            'content' => 'New parking guidelines have been implemented. Each flat is allocated one parking spot. Guest parking is available on a first-come-first-serve basis. Vehicles parked in no-parking zones will be towed at owner\'s expense.',
            'priority' => 'low',
            'status' => 'published',
            'author_id' => $blockChairmanA->id,
            'block_id' => $blockA->id,
            'valid_from' => now()->subDays(5),
            'published_at' => now()->subDays(5),
        ]);
    }
}