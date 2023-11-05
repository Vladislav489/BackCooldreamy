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
        Schema::table('credit_lists', function (Blueprint $table) {
            $table->unsignedSmallInteger('status')->default(0);
        });

        Schema::table('subscriptions_list', function (Blueprint $table) {
            $table->unsignedSmallInteger('status')->default(0);
        });

        Schema::table('premium_lists', function (Blueprint $table) {
            $table->unsignedSmallInteger('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_lists', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('subscriptions_list', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('premium_lists', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
