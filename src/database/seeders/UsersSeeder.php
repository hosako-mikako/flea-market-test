<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => '田中太郎',
                'email' => 'tanaka@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'), 
                'profile_image' => 'images/profiles/tanaka_taro.png',
                'profile_postal_code' => '123-4567',
                'profile_address' => '東京都渋谷区神南1-1-1',
                'profile_building' => 'プロフィールマンション101',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => '佐藤花子',
                'email' => 'sato@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'profile_image' => 'images/profiles/sato_hanako.png',
                'profile_postal_code' => '111-4567',
                'profile_address' => '大阪府堺市南区神南1-1-1',
                'profile_building' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            ]);
        
    }
}
