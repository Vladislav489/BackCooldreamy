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
        Schema::create('arbitrator_payment_rules', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('arbitrator_id');
            $table->tinyInteger('type');
            $table->tinyInteger('rules_pay');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arbitrator_payment_rule');
    }
};
