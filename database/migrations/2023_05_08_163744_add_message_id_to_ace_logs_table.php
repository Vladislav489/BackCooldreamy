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
        Schema::table('ace_logs', function (Blueprint $table) {
            $table->bigInteger('chat_message_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ace_logs', function (Blueprint $table) {
            $table->dropColumn('chat_message_id');
        });
    }
};
