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
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'WavePay',
                'created_at' => $time,
                'updated_at' => $time
            ]
        ]);
    }
}
