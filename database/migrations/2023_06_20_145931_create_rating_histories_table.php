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
        Schema::create('rating_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rating_id');

            $table->unsignedBigInteger('assignment_id');

            $table->foreign('rating_id')->references('id')->on('ratings')->cascadeOnDelete();
            $table->foreign('assignment_id')->references('id')->on('rating_assignments')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_histories');
    }
};
