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
        Schema::create('user_cooperations', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('subid',255);
            $table->string('af_id',255);
            $table->string('app_name',255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cooperations');
    }
};
