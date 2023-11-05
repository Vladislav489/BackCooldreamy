<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void{
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->integer('is_ace')->default(0);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void{
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn('is_ace');
        });
    }
};
