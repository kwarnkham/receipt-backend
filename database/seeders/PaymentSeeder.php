<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $time = now();
        DB::table('payments')->insert([
            [
                'name' => 'KBZPay',
                'type' => 1,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'WavePay',
                'type' => 2,
                'created_at' => $time,
                'updated_at' => $time
            ]
        ]);
    }
}
