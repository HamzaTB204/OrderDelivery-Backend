<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['id' => 1],
            [
                'first_name' => 'Hamza',
                'last_name' => 'Batta',
                'phone' => '1234567890',
                'profile_picture' => null,
                'location' => 'Damascus',
                'locale' => 'en',
                'role' => 'admin',
                'password' => bcrypt('password'),
            ]
        );
        User::updateOrCreate(
            ['id' => 2],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '1234567891',
                'profile_picture' => null,
                'location' => 'Damascus',
                'locale' => 'en',
                'role' => 'driver',
                'password' => bcrypt('password'),
            ]
        );
        User::updateOrCreate(
            ['id' => 3],
            [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'phone' => '1234567892',
                'profile_picture' => null,
                'location' => 'Damascus',
                'locale' => 'en',
                'role' => 'driver',
                'password' => bcrypt('password'),
            ]
        );

    }

}
