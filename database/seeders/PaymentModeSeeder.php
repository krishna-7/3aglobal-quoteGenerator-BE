<?php

namespace Database\Seeders;

use App\Models\PaymentMode;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PaymentMode::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        PaymentMode::insert([
            [
                'payment_provider_id' => 1,
                'name' => 'Card',
                'description' => 'Card',
            ],
            [
                'payment_provider_id' => 2,
                'name' => 'Tabby',
                'description' => 'Tabby',
            ],
            [
                'payment_provider_id' => 2,
                'name' => 'Tamara',
                'description' => 'Tamara',
            ],
        ]);
    }
}
