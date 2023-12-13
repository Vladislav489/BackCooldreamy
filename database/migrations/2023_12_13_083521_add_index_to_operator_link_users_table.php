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
        Schema::table('operator_link_users', function (Blueprint $table) {
            $table->index('operator_id', 'olu_operator_id_index');
            $table->index('user_id', 'olu_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operator_link_users', function (Blueprint $table) {
            $table->dropIndex('olu_operator_id_index');
            $table->dropIndex('olu_user_id_index');
        });
    }
};
