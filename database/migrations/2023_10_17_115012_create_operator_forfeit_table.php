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
        Schema::create('operator_forfeits', function (Blueprint $table) {
            $table->id();
            $table->integer('operator_id');
            $table->integer('message_id');
            $table->integer('chat_id');
            $table->timestamps();
        });
        Schema::table('operator_forfeits', function (Blueprint $table) {
            $table->unique(['operator_id','message_id','chat_id'],'group_unic_operator_forfeits');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ope rator_forfeits');
    }
};
