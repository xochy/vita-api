<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->delete();

        // Create a new user with superAdmin role
        $user = new User([
            'name'              => 'Super Admin',
            'email'             => 'superadmin@mail.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),
        ]);

        $user->save();
        $user->assignRole('superAdmin');

        // Create a new user with admin role
        $user = new User([
            'name'              => 'Admin',
            'email'             => 'admin@mail.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),
        ]);

        $user->save();
        $user->assignRole('admin');

        // Create a new user with user role
        $user = new User([
            'name'              => 'User',
            'email'             => 'user@mail.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),
        ]);

        $user->save();
        $user->assignRole('user');
    }
}
