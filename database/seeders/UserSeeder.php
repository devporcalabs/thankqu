<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Ahmad Fauzan',
            'email' => 'ahmad@thankquu.com',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@thankquu.com',
            'phone' => '089876543210',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);
    }
}
