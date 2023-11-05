<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array[] */
    protected $data = [
        [
            'is_one_time' => true,
            'credits' => 40,
            'discount' => 60,
            'price' => 3.99
        ],
        [
            'is_one_time' => false,
            'credits' => 120,
            'discount' => 0,
            'price' => 20
        ],
        [
            'is_one_time' => false,
            'credits' => 330,
            'discount' => 10,
            'price' => 50
        ],
        [
            'is_one_time' => false,
            'credits' => 1080,
            'discount' => 20,
            'price' => 150
        ],
        [
            'is_one_time' => false,
            'credits' => 2660,
            'discount' => 33,
            'price' => 330
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('credit_lists', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_one_time')->default(false);
            $table->float('credits')->comment('Кредиты');
            $table->float('discount')->comment('Скидка');
            $table->float('price')->comment('Цена');
            $table->timestamps();
        });

        foreach ($this->data as $datum) {
            \App\Models\User\CreditList::create($datum);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_lists');
    }
};
