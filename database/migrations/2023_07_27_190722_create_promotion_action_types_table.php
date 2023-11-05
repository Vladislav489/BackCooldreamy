<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PromotionActionType;


return new class extends Migration
{
    protected $data = [
        1 => [
            'id' => 1,
            'info' => 'Новая регистрация',
            'system_enum' => PromotionActionType::NEW_REGISTER
        ],
        2 => [
            'id' => 2,
            'info' => 'Потратил первые 10 кредитов',
            'system_enum' => PromotionActionType::FIRST_MESSAGES
        ]
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promotion_action_types', function (Blueprint $table) {
            $table->id();
            $table->string('info');
            $table->string('system_enum');
            $table->timestamps();
        });

        foreach ($this->data as $item) {
            PromotionActionType::create($item);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_action_types');
    }
};
