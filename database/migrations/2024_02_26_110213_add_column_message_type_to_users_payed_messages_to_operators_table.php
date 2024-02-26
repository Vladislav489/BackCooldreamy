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
        Schema::table('users_payed_messages_to_operators', function (Blueprint $table) {
            $table->unsignedTinyInteger('message_type')->comment('1 - text, 2 - image, 3 - video, 4 - sticker, 5 - gift')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_payed_messages_to_operators', function (Blueprint $table) {
            $table->dropColumn('message_type');
        });
    }
};
