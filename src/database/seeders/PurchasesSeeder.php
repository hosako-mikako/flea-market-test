<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchasesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('purchases')->insert([
            [
                'user_id' => 2,
                'product_id' => 1,
                'purchased_at' => now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'product_id' => 2,
                'purchased_at' => now()->subDays(1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
