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
        Schema::table('credits_reals', function (Blueprint $table) {
            $table->index('user_id', 'cr_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credits_reals', function (Blueprint $table) {
            $table->dropIndex('cr_user_id_index');
        });
    }
};
