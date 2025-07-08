<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CategoriesSeeder::class,
            UsersSeeder::class,
            ShippingAddressesSeeder::class,
            ProductsSeeder::class,
            FavoritesSeeder::class,
            PurchasesSeeder::class,
        ]);
    }
}
