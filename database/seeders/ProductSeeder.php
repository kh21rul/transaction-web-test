<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            // Makanan
            ['category_id' => 1, 'name' => 'Nasi Goreng Instan', 'price' => 15000, 'stock' => 50],
            ['category_id' => 1, 'name' => 'Mie Instan Goreng', 'price' => 3500, 'stock' => 100],

            // Minuman
            ['category_id' => 2, 'name' => 'Air Mineral 600ml', 'price' => 4000, 'stock' => 100],
            ['category_id' => 2, 'name' => 'Teh Botol Sosro', 'price' => 5000, 'stock' => 80],
            ['category_id' => 2, 'name' => 'Kopi Sachet', 'price' => 2000, 'stock' => 200],

            // Snack
            ['category_id' => 3, 'name' => 'Keripik Singkong', 'price' => 10000, 'stock' => 40],
            ['category_id' => 3, 'name' => 'Chitato Sapi Panggang', 'price' => 12000, 'stock' => 30],

            // Rumah Tangga
            ['category_id' => 4, 'name' => 'Sabun Cuci Piring', 'price' => 15000, 'stock' => 25],
            ['category_id' => 4, 'name' => 'Deterjen Bubuk 1kg', 'price' => 20000, 'stock' => 20],

            // Perawatan Diri
            ['category_id' => 5, 'name' => 'Shampoo Sachet', 'price' => 1000, 'stock' => 200],
            ['category_id' => 5, 'name' => 'Pasta Gigi', 'price' => 8000, 'stock' => 50],
        ]);
    }
}
