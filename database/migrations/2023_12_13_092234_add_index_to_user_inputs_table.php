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
        Schema::table('user_inputs', function (Blueprint $table) {
            $table->index('user_id', 'ui_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_inputs', function (Blueprint $table) {
            $table->dropIndex('ui_user_id_index');
        });
    }
};
