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
        Schema::table('subscriptions_list', function (Blueprint $table) {
            $table->integer('old_price');
        });
        Schema::table('credit_lists', function (Blueprint $table) {
            $table->integer('old_price');
        });
        Schema::table('premium_lists', function (Blueprint $table) {
            $table->integer('old_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions_list', function (Blueprint $table) {
            $table->dropColumn('old_price');
        });
        Schema::table('credit_lists', function (Blueprint $table) {
            $table->dropColumn('old_price');
        });
        Schema::table('premium_lists', function (Blueprint $table) {
            $table->dropColumn('old_price');
        });
    }
};
