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
        Schema::table('credit_logs', function (Blueprint $table) {
            $table->index('user_id', 'cl_user_id_index');
            $table->index('other_user_id', 'cl_other_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_logs', function (Blueprint $table) {
            $table->dropIndex('cl_user_id_index');
            $table->dropIndex('cl_other_user_id_index');
        });
    }
};
