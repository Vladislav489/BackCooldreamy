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
        Schema::table('working_shift_crons', function (Blueprint $table) {
            $table->index('user_id', 'wsc_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('working_shift_crons', function (Blueprint $table) {
            $table->dropIndex('wsc_user_id_index');
        });
    }
};
