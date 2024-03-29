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
        Schema::table('letters', function (Blueprint $table) {
            $table->boolean('is_anket_favorite_by_first_user')->default(false);
            $table->boolean('is_anket_favorite_by_second_user')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropColumn('is_anket_favorite_by_first_user');
            $table->dropColumn('is_anket_favorite_by_second_user');
        });
    }
};
