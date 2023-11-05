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
        Schema::create('working_shift_anser_operators', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('operator_id');
            $table->bigInteger('ancet_id');
            $table->bigInteger('chat_id');
            $table->bigInteger('message_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_shift_anser_operators');
    }
};
