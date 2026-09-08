<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@agencia5801.local'],
            [
                'name' => 'Administrador del Sistema',
                'dni' => '00000001',
                'rol' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make('Admin1234!'),
            ]
        );
    }
}
