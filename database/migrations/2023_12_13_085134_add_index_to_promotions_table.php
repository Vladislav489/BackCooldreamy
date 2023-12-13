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
        Schema::table('promotions', function (Blueprint $table) {
            $table->index('subscription_id', 'promotions_subscription_id_index');
            $table->index('premium_id', 'promotions_premium_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropIndex('promotions_subscription_id_index');
            $table->dropIndex('promotions_premium_id_index');
        });
    }
};
