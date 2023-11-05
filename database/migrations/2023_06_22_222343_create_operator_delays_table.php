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
        Schema::create('operator_delays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id');
            $table->float('time')->default(0)->comment('Время которое было указано(3:30)');
            $table->float('delay')->default(0)->comment('Задержка в ответе');

            $table->foreign('operator_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_delays');
    }
};
