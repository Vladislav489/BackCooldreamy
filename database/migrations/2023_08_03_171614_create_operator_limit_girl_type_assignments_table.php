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
        Schema::create('operator_limit_girl_type_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_type_id');
            $table->float('chance');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_limit_girl_type_assignments');
    }
};
