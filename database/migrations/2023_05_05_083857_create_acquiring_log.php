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
        Schema::create('acquiring_log', function (Blueprint $table) {
            $table->id();
            $table->integer("user_id");
            $table->integer("service type");
            $table->string("transaction_id");
            $table->integer("cost");
            $table->integer("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acquiring_log');
    }
};
