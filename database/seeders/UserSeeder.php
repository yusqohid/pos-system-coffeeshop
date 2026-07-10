<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@pos.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ],
        );
    }
}
