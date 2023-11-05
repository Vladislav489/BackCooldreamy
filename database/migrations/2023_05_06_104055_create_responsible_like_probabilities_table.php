<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('responsible_like_probabilities', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('like_count')->unsigned();
            $table->decimal('probability', 8, 3);
            $table->timestamps();
        });

        DB::table('responsible_like_probabilities')->insert([
            'like_count' => 100,
            'probability' => 0.1
        ]);
        DB::table('responsible_like_probabilities')->insert([
            'like_count' => 500,
            'probability' => 0.05
        ]);
        DB::table('responsible_like_probabilities')->insert([
            'like_count' => 1000,
            'probability' => 0.01
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responsible_like_probabilities');
    }
};
