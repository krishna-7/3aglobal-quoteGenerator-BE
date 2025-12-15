<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TransactionType::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        TransactionType::insert([
            [
                'name' => 'Domestic',
                'description' => 'Domestic',
                'payment_provider_id' => 1,
            ],
            [
                'name' => 'International',
                'description' => 'International',
                'payment_provider_id' => 2,
            ]
        ]);
    }
}
