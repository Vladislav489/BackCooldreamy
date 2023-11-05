<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Promotion::create([
            'type' => 1,
            'activation_type_id' => 1,
            'hours' => 48,
            'credits' => 100,
            'status' => 2,
            'benefit' => 90,
            'subscription_id' => null,
            'premium_id' => null,
            'price' => 3.99
        ]);

        Promotion::create([
            'type' => 1,
            'activation_type_id' => 2,
            'hours' => 12,
            'credits' => 50,
            'status' => 2,
            'benefit' => 90,
            'subscription_id' => null,
            'premium_id' => null,
            'price' => 0.99
        ]);
    }
}
