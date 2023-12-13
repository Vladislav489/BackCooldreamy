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
        Schema::table('working_shift_anser_operators', function (Blueprint $table) {
            $table->index('operator_id', 'wsao_operator_id_index');
            $table->index('ancet_id', 'wsao_ancet_id_index');
            $table->index('chat_id', 'wsao_chat_id_index');
            $table->index('message_id', 'wsao_message_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('working_shift_anser_operators', function (Blueprint $table) {
            $table->dropIndex('wsao_operator_id_index');
            $table->dropIndex('wsao_ancet_id_index');
            $table->dropIndex('wsao_chat_id_index');
            $table->dropIndex('wsao_message_id_index');
        });
    }
};
