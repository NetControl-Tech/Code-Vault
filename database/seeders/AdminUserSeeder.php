<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * إنشاء مستخدم مسؤول افتراضي
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'mahmodaborakika2@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        // Assign admin role
        $admin->assignRole('super-admin');
    }
}
