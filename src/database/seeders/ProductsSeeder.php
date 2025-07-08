<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $productId1 = DB::table('products')->insertGetId([
            'user_id' => 1, // 田中太郎
            'name' => '腕時計',
            'description' => 'スタイリッシュなデザインのメンズ腕時計',
            'price' => 15000,
            'condition' => 2, // 良好
            'brand' => 'Armani',
            'status' => 1,
            'image_path' => 'images/products/armani_clock.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId2 = DB::table('products')->insertGetId([
            'user_id' => 2, // 佐藤花子
            'name' => 'HDD',
            'description' => '高速で信頼性の高いハードディスク',
            'price' => 5000,
            'condition' => 3, // 目立った傷や汚れなし
            'brand' => 'Seagate',
            'status' => 1,
            'image_path' => 'images/products/hard_disk.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId3 = DB::table('products')->insertGetId([
            'user_id' => 1,
            'name' => '玉ねぎ3束',
            'description' => '新鮮な玉ねぎ3束のセット',
            'price' => 300,
            'condition' => 4, // やや傷や汚れあり
            'brand' => '淡路島産',
            'status' => 1,
            'image_path' => 'images/products/onions.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId4 = DB::table('products')->insertGetId([
            'user_id' => 2,
            'name' => '革靴',
            'description' => 'クラシックなデザインの革靴',
            'price' => 4000,
            'condition' => 5, // 状態が悪い
            'brand' => 'リーガル',
            'status' => 1,
            'image_path' => 'images/products/leather_shoes.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId5 = DB::table('products')->insertGetId([
            'user_id' => 1,
            'name' => 'ノートPC',
            'description' => '高性能なノートパソコン',
            'price' => 45000,
            'condition' => 2, // 良好
            'brand' => 'Dell',
            'status' => 1,
            'image_path' => 'images/products/laptop.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId6 = DB::table('products')->insertGetId([
            'user_id' => 2,
            'name' => 'マイク',
            'description' => '高音質のレコーディング用マイク',
            'price' => 8000,
            'condition' => 3, // 目立った傷や汚れなし
            'brand' => 'Audio-Technica',
            'status' => 1,
            'image_path' => 'images/products/microphone.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId7 = DB::table('products')->insertGetId([
            'user_id' => 1,
            'name' => 'ショルダーバッグ',
            'description' => 'おしゃれなショルダーバッグ',
            'price' => 3500,
            'condition' => 4, // やや傷や汚れあり
            'brand' => 'コーチ',
            'status' => 1,
            'image_path' => 'images/products/shoulder_bag.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId8 = DB::table('products')->insertGetId([
            'user_id' => 2,
            'name' => 'タンブラー',
            'description' => '使いやすいタンブラー',
            'price' => 500,
            'condition' => 5, // 状態が悪い
            'brand' => 'スターバックス',
            'status' => 1,
            'image_path' => 'images/products/tumbler.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId9 = DB::table('products')->insertGetId([
            'user_id' => 1,
            'name' => 'コーヒーミル',
            'description' => '手動のコーヒーミル',
            'price' => 4000,
            'condition' => 2, // 良好
            'brand' => 'カリタ',
            'status' => 1,
            'image_path' => 'images/products/coffee_mill.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId10 = DB::table('products')->insertGetId([
            'user_id' => 2,
            'name' => 'メイクセット',
            'description' => '便利なメイクアップセット',
            'price' => 2500,
            'condition' => 3, // 目立った傷や汚れなし
            'brand' => '資生堂',
            'status' => 1,
            'image_path' => 'images/products/makeup_set.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 商品カテゴリの関連付け
        DB::table('product_categories')->insert([
            // 腕時計 → メンズ、アクセサリー
            ['product_id' => $productId1, 'category_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $productId1, 'category_id' => 13, 'created_at' => now(), 'updated_at' => now()],

            // HDD → 家電、PC
            ['product_id' => $productId2, 'category_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $productId2, 'category_id' => 8, 'created_at' => now(), 'updated_at' => now()],

            // 玉ねぎ → キッチン、食品
            ['product_id' => $productId3, 'category_id' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $productId3, 'category_id' => 11, 'created_at' => now(), 'updated_at' => now()],

            // 革靴 → メンズ、ファッション
            ['product_id' => $productId4, 'category_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $productId4, 'category_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // ノートPC → 家電、PC
            ['product_id' => $productId5, 'category_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $productId5, 'category_id' => 8, 'created_at' => now(), 'updated_at' => now()],

            // マイク → 家電、楽器
            ['product_id' => $productId6, 'category_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $productId6, 'category_id' => 9, 'created_at' => now(), 'updated_at' => now()],

            // ショルダーバッグ → レディース、ファッション
            ['product_id' => $productId7, 'category_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $productId7, 'category_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // タンブラー → キッチン、その他
            ['product_id' => $productId8, 'category_id' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $productId8, 'category_id' => 16, 'created_at' => now(), 'updated_at' => now()],

            // コーヒーミル → キッチン、家電
            ['product_id' => $productId9, 'category_id' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $productId9, 'category_id' => 2, 'created_at' => now(), 'updated_at' => now()],

            // メイクセット → レディース、コスメ
            ['product_id' => $productId10, 'category_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $productId10, 'category_id' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
