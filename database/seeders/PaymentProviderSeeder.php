<?php

namespace Database\Seeders;

use App\Models\PaymentProvider;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PaymentProvider::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        PaymentProvider::insert([
            [
                'name' => 'ccAvenue',
                'description' => 'ccAvenue',
                'charge' => 3
            ],
            [
                'name' => 'PayMob',
                'description' => 'PayMob',
                'charge' => 11
            ]
        ]);
    }
}
