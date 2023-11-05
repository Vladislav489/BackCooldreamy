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
        Schema::create('operator_link_users', function (Blueprint $table) {
            $table->id();
            $table->integer('operator_id');
            $table->integer('user_id');
            $table->boolean('operator_work');
            $table->boolean('admin_work');
            $table->string('description', 200);
            $table->boolean('disabled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_link_users');
    }
};
