<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operator_chat_limits', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('man_id')->unsigned();
            $table->bigInteger('girl_id')->unsigned();
            $table->decimal('limits', 5, 3)->unsigned()->default(0);
            $table->timestamps();

            $table->foreign('man_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('girl_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_chat_limits');
    }
};
