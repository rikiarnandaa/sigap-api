<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@sigap.local',
                'jabatan' => 'Pengelola Sistem',
                'is_active' => true,
                'password' => 'admin123@',
            ]
        );

        // User nonaktif untuk pengujian
        User::updateOrCreate(
            ['username' => 'nonaktif'],
            [
                'name' => 'User Nonaktif',
                'is_active' => false,
                'password' => 'admin123@',
            ]
        );
    }
}