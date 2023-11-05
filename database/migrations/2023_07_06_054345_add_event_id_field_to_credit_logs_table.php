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
            $table->unsignedBigInteger('other_user_id')->nullable();
            $table->unsignedBigInteger('action_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_logs', function (Blueprint $table) {
            $table->dropColumn('other_user_id');
            $table->dropColumn('action_type');
        });
    }
};
