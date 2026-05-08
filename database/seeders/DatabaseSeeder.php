<?php

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@lostandfound.test'],
            [
                'name' => 'System Admin',
                'student_id' => 'ADM-2026-001',
                'phone' => '09171234567',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ],
        );

        $student = User::updateOrCreate(
            ['email' => 'student@lostandfound.test'],
            [
                'name' => 'Pretty Mae Otero',
                'student_id' => 'IT9-2026-015',
                'phone' => '09951234567',
                'role' => 'student',
                'password' => Hash::make('password'),
            ],
        );

        $lostWallet = Item::updateOrCreate(
            ['slug' => 'black-wallet-near-library'],
            [
                'user_id' => $student->id,
                'type' => 'lost',
                'category' => 'wallet',
                'title' => 'Black wallet near the library',
                'description' => 'Leather wallet with school ID, ATM card, and a small family photo inside.',
                'location' => 'Main Library, 2nd Floor Reading Area',
                'reported_at' => now()->subDays(2),
                'status' => 'under_review',
                'contact_name' => $student->name,
                'contact_email' => $student->email,
                'contact_phone' => $student->phone,
                'reward_amount' => 500,
            ],
        );

        $foundUsb = Item::updateOrCreate(
            ['slug' => 'blue-usb-drive-in-lab-3'],
            [
                'user_id' => $admin->id,
                'type' => 'found',
                'category' => 'electronics',
                'title' => 'Blue USB drive found in Lab 3',
                'description' => '16GB USB flash drive found after the afternoon networking class.',
                'location' => 'Computer Laboratory 3',
                'reported_at' => now()->subDay(),
                'status' => 'under_review',
                'contact_name' => $admin->name,
                'contact_email' => $admin->email,
                'contact_phone' => $admin->phone,
            ],
        );

        Claim::updateOrCreate(
            [
                'item_id' => $foundUsb->id,
                'claimant_id' => $student->id,
            ],
            [
                'message' => 'I believe this is mine. It has my thesis backup and OOP project source code.',
                'proof_details' => 'The drive contains folders named CAPSTONE-FINAL and OTERO-IT9.',
                'status' => 'pending',
            ],
        );
    }
}
