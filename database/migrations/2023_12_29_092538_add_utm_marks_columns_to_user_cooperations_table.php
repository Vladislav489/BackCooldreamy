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
        Schema::table('user_cooperations', function (Blueprint $table) {
            $table->string('utm_source', 255)->nullable();
            $table->string('utm_medium', 255)->nullable();
            $table->string('utm_campaign', 255)->nullable();
            $table->string('utm_term', 255)->nullable();
            $table->string('utm_advertiser', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_cooperations', function (Blueprint $table) {
            $table->dropColumn('utm_source');
            $table->dropColumn('utm_medium');
            $table->dropColumn('utm_campaign');
            $table->dropColumn('utm_term');
            $table->dropColumn('utm_advertiser');
        });
    }
};
