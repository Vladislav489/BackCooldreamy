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
        Schema::create('ace_system_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("user_id");
            $table->integer("group_id")->default(0);
            $table->tinyInteger('active')->default(1);
            $table->bigInteger("last_assignments_id");
            $table->bigInteger("last_assignments_sort");
            $table->integer("step_cron_counter");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ace_system_users');
    }
};
