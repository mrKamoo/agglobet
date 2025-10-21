<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'admin',
            'email' => 'admin@agglobet.local',
            'email_verified_at' => now(),
            'password' => Hash::make('admin'),
            'is_admin' => true,
            'exclude_from_leaderboard' => true,
        ]);
    }
}
