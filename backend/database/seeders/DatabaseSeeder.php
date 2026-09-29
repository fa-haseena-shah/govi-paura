<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\Admin;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->withRole(UserType::Admin)->create([
            'full_name' => 'System Admin',
            'email' => 'admin@govipaura.test',
            'phone' => '0770000000',
        ]);

        // admins password is "password"
        Admin::create([
            'user_id'     => $admin->id,
            'designation' => 'System Administrator',
        ]);
    }
}