<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'nama_depan' => 'admin1',
            'nama_belakang' => 'hehe',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin@gmail.com'),
            'role_type' => 1,
            'gender' => 'Laki'
        ]);
        DB::table('users')->insert([
            'nama_depan' => 'ali',
            'nama_belakang' => 'topan',
            'email' => 'alitopan@gmail.com',
            'password' => Hash::make('alitopan@gmail.com'),
            'role_type' => 0,
            'gender' => 'Laki'
        ]);
    }
}
