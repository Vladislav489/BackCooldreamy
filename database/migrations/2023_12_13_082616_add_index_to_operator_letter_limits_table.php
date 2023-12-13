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
        Schema::table('operator_letter_limits', function (Blueprint $table) {
            $table->index('letter_id', 'oll_letter_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operator_letter_limits', function (Blueprint $table) {
            $table->dropIndex('oll_letter_id_index');
        });
    }
};
