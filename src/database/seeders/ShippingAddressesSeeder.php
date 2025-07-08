<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingAddressesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shipping_addresses')->insert([
            [
                'user_id' => 1,
                'postal_code' => '123-4567',
                'address' => '東京都渋谷区神南1-1-1',
                'building' => 'サンプルマンション102',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'postal_code' => '456-7890',
                'address' => '東京都新宿区西新宿2-2-2',
                'building' => 'オフィスビル5F',
                'is_default' => false,  // サブ送付先
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'postal_code' => '111-4567',
                'address' => '大阪府堺市南区神南1-1-1',
                'building' => null,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

    }
}
