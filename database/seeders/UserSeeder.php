<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $time = now();
        DB::table('users')->insert([
            [
                'name' => 'admin',
                'mobile' => '911',
                'password' => bcrypt('0330'),
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'admin2',
                'mobile' => '922',
                'password' => bcrypt('0330'),
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'UKM',
                'mobile' => '09-',
                'password' => bcrypt('0330'),
                'created_at' => $time,
                'updated_at' => $time
            ]
        ]);

        DB::table('roles')->insert([
            [
                'name' => 'admin',
                'created_at' => $time,
                'updated_at' => $time
            ]
        ]);

        DB::table('user_role')->insert([
            [
                'user_id' => 1,
                'role_id' => 1,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'user_id' => 2,
                'role_id' => 1,
                'created_at' => $time,
                'updated_at' => $time
            ]
        ]);
    }
}
