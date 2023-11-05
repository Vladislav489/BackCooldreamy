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
        Schema::create('routing_users', function (Blueprint $table) {
            $table->id();
            $table->string('url',1000);
            $table->string('url_from',1000);
            $table->string('os');
            $table->string('langBr');
            $table->string('typeBr');
            $table->string('typeDivece');
            $table->bigInteger('user_id')->nullable();
            $table->json("tag")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routing_users');
    }
};
