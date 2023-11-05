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
        Schema::create('operator_letter_limits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('man_id');
            $table->unsignedBigInteger('girl_id');
            $table->double('limits')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_letter_limits');
    }
};
