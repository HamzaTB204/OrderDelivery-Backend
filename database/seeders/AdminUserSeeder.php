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
    }

}
