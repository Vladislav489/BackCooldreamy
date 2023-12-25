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
        Schema::table('favorite_profile_probabilities', function (Blueprint $table) {
            $table->smallInteger('user_group')->nullable();
        });

        Schema::table('like_profile_probabilities', function (Blueprint $table) {
            $table->smallInteger('user_group')->nullable();
        });

        Schema::table('watch_profile_probabilities', function (Blueprint $table) {
            $table->smallInteger('user_group')->nullable();
        });

        \App\Models\FavoriteProfileProbability::insert([
            ['profile_type_id' => 1, 'probability' => 0.2, 'user_group' => 2],
            ['profile_type_id' => 2, 'probability' => 0.4, 'user_group' => 2],
            ['profile_type_id' => 3, 'probability' => 0.22, 'user_group' => 2],
            ['profile_type_id' => 4, 'probability' => 0.05, 'user_group' => 2],
        ]);

        \App\Models\LikeProfileProbability::insert([
            ['profile_type_id' => 1, 'probability' => 0.05, 'user_group' => 2],
            ['profile_type_id' => 2, 'probability' => 0.05, 'user_group' => 2],
            ['profile_type_id' => 3, 'probability' => 0.05, 'user_group' => 2],
            ['profile_type_id' => 4, 'probability' => 0.05, 'user_group' => 2],
        ]);

        \App\Models\WatchProfileProbability::insert([
            ['profile_type_id' => 1, 'probability' => 0.3, 'user_group' => 2],
            ['profile_type_id' => 2, 'probability' => 0.3, 'user_group' => 2],
            ['profile_type_id' => 3, 'probability' => 0.25, 'user_group' => 2],
            ['profile_type_id' => 4, 'probability' => 0.7, 'user_group' => 2],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorite_profile_probabilities', function (Blueprint $table) {
            $table->dropColumn('user_group');
        });

        Schema::table('like_profile_probabilities', function (Blueprint $table) {
            $table->dropColumn('user_group');
        });

        Schema::table('watch_profile_probabilities', function (Blueprint $table) {
            $table->dropColumn('user_group');
        });
    }
};
