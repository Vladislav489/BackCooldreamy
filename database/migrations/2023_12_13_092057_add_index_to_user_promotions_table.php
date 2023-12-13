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
        Schema::table('user_promotions', function (Blueprint $table) {
            $table->index('user_id', 'up_user_id_index');
            $table->index('promotion_id', 'up_promotion_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_promotions', function (Blueprint $table) {
            $table->dropIndex('up_user_id_index');
            $table->dropIndex('up_promotion_id_index');
        });
    }
};
