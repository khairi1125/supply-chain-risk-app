<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@supply.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'user@supply.com',
            'password' => bcrypt('user123'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
