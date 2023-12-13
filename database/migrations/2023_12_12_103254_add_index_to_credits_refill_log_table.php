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
        Schema::table('credits_refill_logs', function (Blueprint $table) {
            $table->index('user_id', 'crl_user_id_index');
            $table->index('second_user_id', 'crl_second_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credits_refill_logs', function (Blueprint $table) {
            $table->dropIndex('crl_user_id_index');
            $table->dropIndex('crl_second_user_id_index');
        });
    }
};
