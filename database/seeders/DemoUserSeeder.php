<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Administrator Pemerintah',
                'email' => 'governance@laporbah.test',
                'password' => 'GovLaporBah!2026',
                'role' => 'governance',
            ],
            [
                'name' => 'Petugas Lapangan 1',
                'email' => 'officer1@laporbah.test',
                'password' => 'Officer1!2026',
                'role' => 'officer',
            ],
            [
                'name' => 'Petugas Lapangan 2',
                'email' => 'officer2@laporbah.test',
                'password' => 'Officer2!2026',
                'role' => 'officer',
            ],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@laporbah.test',
                'password' => 'SuperAdmin!2026',
                'role' => 'superadmin',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrNew([
                'email' => $account['email'],
            ]);

            $user->name = $account['name'];
            $user->password = $account['password'];
            $user->forceFill([
                'role' => $account['role'],
                'email_verified_at' => now(),
            ]);

            $user->save();
        }
    }
}
