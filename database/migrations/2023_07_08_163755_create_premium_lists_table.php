<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $data = [
        [
            'duration' => 1,
            'discount' => 0,
            'price' => 25
        ],
        [
            'duration' => 2,
            'discount' => 9,
            'price' => 45
        ],
        [
            'duration' => 4,
            'discount' => 25,
            'price' => 75
        ]
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('premium_lists', function (Blueprint $table) {
            $table->id();
            $table->float('duration')->comment('Недели');
            $table->float('discount')->comment('Скидка');
            $table->float('price')->comment('Цена');
            $table->timestamps();
        });

        foreach ($this->data as $datum) {
            \App\Models\User\PremiumList::create($datum);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_lists');
    }
};
