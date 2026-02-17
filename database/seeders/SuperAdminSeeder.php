<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminEmail = env('SUPER_ADMIN_EMAIL', 'admin@barbersaas.com');

        if (!User::where('email', $superAdminEmail)->exists()) {
            $user = User::create([
                'name' => 'Super Admin',
                'email' => $superAdminEmail,
                'email_verified_at' => now(),
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'password')),
                'remember_token' => Str::random(10),
                'role' => 'super_admin',
                'shop_id' => null, // Super Admin has no shop
            ]);

            $user->assignRole('super_admin');
        }
    }
}
