<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ace_sending_probabilities', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('profile_type_id');
            $table->float('probability');
            $table->smallInteger('user_group');
        });
        \App\Models\AceSendingProbability::insert([
            ['profile_type_id' => 1, 'probability' => 0.4, 'user_group' => 1],
            ['profile_type_id' => 2, 'probability' => 0.4, 'user_group' => 1],
            ['profile_type_id' => 3, 'probability' => 0.5, 'user_group' => 1],
            ['profile_type_id' => 4, 'probability' => 0.6, 'user_group' => 1],
            ['profile_type_id' => 1, 'probability' => 0.2, 'user_group' => 2],
            ['profile_type_id' => 2, 'probability' => 0.2, 'user_group' => 2],
            ['profile_type_id' => 3, 'probability' => 0.35, 'user_group' => 2],
            ['profile_type_id' => 4, 'probability' => 0.5, 'user_group' => 2],

        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ace_sending_probabilities');
    }
};
