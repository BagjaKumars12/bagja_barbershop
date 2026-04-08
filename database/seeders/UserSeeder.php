<?php
// database/seeders/UserSeeder.php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'username' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ],
            [
                'username' => 'Kasir',
                'email' => 'kasir@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'kasir'
            ],
            [
                'username' => 'Owner',
                'email' => 'owner@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'owner'
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}