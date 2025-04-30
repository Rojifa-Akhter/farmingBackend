<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Create an admin user
         User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'role' => 'super_admin',
            'address' => 'Dhaka, Bangladesh',
            'password' => Hash::make('12345678'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);


        User::create([
            'name' => 'investor',
            'email' => 'investor@gmail.com',
            'role' => 'investor',
            'address' => 'Dhaka, Bangladesh',
            'password' => Hash::make('12345678'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        User::create([
            'name' => 'farmer',
            'email' => 'farmer@gmail.com',
            'role' => 'farmer',
            'address' => 'Dhaka, Bangladesh',
            'password' => Hash::make('12345678'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        // User::create([
        //     'name' => 'user',
        //     'email' => 'user@gmail.com',
        //     'role' => 'user',
        //     'address' => 'Dhaka, Bangladesh',
        //     'password' => Hash::make('123456'),
        //     'status' => 'active',
        //     'email_verified_at' => now(),
        // ]);
    }
}
