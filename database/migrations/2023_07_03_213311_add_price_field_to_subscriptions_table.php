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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('one_time')->default(false);
            $table->unsignedBigInteger('count_letters')->default(0);
            $table->unsignedBigInteger('count_watch_or_send_photos')->default(0);
            $table->unsignedBigInteger('count_watch_or_send_video')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('one_time');
            $table->dropColumn('count_letters');
            $table->dropColumn('count_watch_or_send_photos');
            $table->dropColumn('count_watch_or_send_video');
        });
    }
};
