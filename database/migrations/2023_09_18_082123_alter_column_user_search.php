<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('search_age_from');
            $table->integer('search_age_to');
            $table->string('search_gender');
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('search_age_from');
            $table->dropColumn('search_age_to');
            $table->dropColumn('search_gender');
        });
    }
};
