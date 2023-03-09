<?php

namespace Database\Seeders;

use Faker\Core\Number;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TableASeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i =0; $i <= 20; $i++) {
            DB::connection('mysqlA')->table('table_A')->insert([
                'name' => Str::random(10),
                'note' => Str::random(20) . ' Note',
                'price' => (new Number())->randomNumber(5),
                'price_total' => (new Number())->randomNumber(6),
                'type' => array_rand(['t1', 't2', 't3']),
                'status' => array_rand([1, 2, 3]),
                'created_at' => now(),
            ]);

            sleep(2);
        }
    }
}
