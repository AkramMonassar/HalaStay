<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@halastay.com'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('Admin@123456'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}