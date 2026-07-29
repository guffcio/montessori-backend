<?php

namespace Database\Seeders;

use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'email' => 'admin@example.com',
            'phone' => '+48999999999',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
        ]);
    }
}
