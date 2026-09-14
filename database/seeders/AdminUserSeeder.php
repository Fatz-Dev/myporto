<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_ALLOWED_EMAIL', 'admin@example.com');
        
        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Administrator',
                'password' => Hash::make(str()->random(16)), // Random password, login is via OTP/SSO
                // If the users table has email_verified_at, set it
                'email_verified_at' => now(),
            ]
        );
    }
}
