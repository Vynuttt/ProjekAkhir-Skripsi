<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Dovon Henkang',
                'email' => 'Dovonkang27@gmail.com',
                'password' => 'password',
                'role' => 'owner',
            ],
            [
                'name' => 'Hendri Handoko',
                'email' => 'hendrihan20@gmail.com',
                'password' => 'password',
                'role' => 'admin',
            ],
            [
                'name' => 'Kasir JayaMuncul',
                'email' => 'kasir@jayamuncul.test',
                'password' => 'password',
                'role' => 'kasir',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']], // cari berdasarkan email
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => Hash::make($user['password']), // hash password
                    'role' => $user['role'],
                    'is_active' => true,
                ]
            );
        }
    }
}
