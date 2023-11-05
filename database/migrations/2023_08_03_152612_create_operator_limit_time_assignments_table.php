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
        Schema::create('operator_limit_time_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('limit_count');
            $table->unsignedBigInteger('profile_type_id');
            $table->unsignedBigInteger('time_from');
            $table->unsignedBigInteger('time_to');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_limit_time_assignments');
    }
};
