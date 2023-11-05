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
        Schema::create('limit_system_limit_assignments', function (Blueprint $table) {
            $table->id();
            $table->integer('group_id');
            $table->integer('sort');
            $table->integer('step_from');
            $table->integer('step_to');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limit_system_limit_assignments');
    }
};
