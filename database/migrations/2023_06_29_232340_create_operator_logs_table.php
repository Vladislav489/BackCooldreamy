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
        Schema::create('operator_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id');
            $table->unsignedBigInteger('anket_id');
            $table->unsignedBigInteger('man_id');
            $table->text('log');

            $table->foreign('operator_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('anket_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('man_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_logs');
    }
};
