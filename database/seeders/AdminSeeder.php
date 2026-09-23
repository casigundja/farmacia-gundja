<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('ADMIN_PASSWORD');
        if (! is_string($password) || strlen($password) < 8) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@farmacia.local')],
            [
                'name' => env('ADMIN_NAME', 'Administrador'),
                'password' => $password,
                'role' => 'admin',
                'status' => true,
            ],
        );
    }
}
