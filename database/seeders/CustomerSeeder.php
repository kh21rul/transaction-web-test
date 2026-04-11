<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('customers')->insert([
            [
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
                'address' => 'Jakarta'
            ],
            [
                'name' => 'Siti Aminah',
                'phone' => '082345678901',
                'address' => 'Bandung'
            ],
            [
                'name' => 'Andi Wijaya',
                'phone' => '083456789012',
                'address' => 'Surabaya'
            ],
            [
                'name' => 'Dewi Lestari',
                'phone' => '084567890123',
                'address' => 'Yogyakarta'
            ],
            [
                'name' => 'Rudi Hartono',
                'phone' => '085678901234',
                'address' => 'Semarang'
            ],
        ]);
    }
}
