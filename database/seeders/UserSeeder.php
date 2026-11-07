<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the default staff accounts.
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Owner KafWeb', 'username' => 'admin', 'password' => 'admin123', 'role' => UserRole::Admin],
            ['name' => 'Kasir Pagi', 'username' => 'kasir', 'password' => 'kasir123', 'role' => UserRole::Kasir],
            ['name' => 'Kasir Sore', 'username' => 'kasir2', 'password' => 'kasir123', 'role' => UserRole::Kasir],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                attributes: ['username' => $account['username']],
                values: [
                    'name' => $account['name'],
                    'password' => $account['password'],
                    'role' => $account['role'],
                ],
            );
        }
    }
}
