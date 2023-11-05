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
        Schema::table('letters', function (Blueprint $table) {
            $table->boolean('deleted_by_first_user')->default(false);
            $table->boolean('deleted_by_second_user')->default(false);
            $table->string('uuid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropColumn('deleted_by_first_user');
            $table->dropColumn('deleted_by_second_user');
            $table->dropColumn('uuid');
        });
    }
};
