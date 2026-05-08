<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create admin
$admin = User::create([
    'email' => 'admin@lostandfound.test',
    'name' => 'System Admin',
    'student_id' => 'ADM-2026-001',
    'phone' => '09171234567',
    'role' => 'admin',
    'password' => Hash::make('password'),
]);

// Create student
$student = User::create([
    'email' => 'student@lostandfound.test',
    'name' => 'Pretty Mae Otero',
    'student_id' => 'IT9-2026-015',
    'phone' => '09951234567',
    'role' => 'student',
    'password' => Hash::make('password'),
]);

echo "Users created successfully!\n";
echo "Total users: " . User::count() . "\n";
echo "\nAdmin: admin@lostandfound.test / password\n";
echo "Student: student@lostandfound.test / password\n";
