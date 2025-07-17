<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('super_admin')->insert([
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'password' => Hash::make('superadmin@gmail.com'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Super Admin 2',
                'email' => 'superadmin2@gmail.com',
                'password' => Hash::make('superadmin123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
