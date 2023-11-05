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
        Schema::create('operator_chat_limit_assigments', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('type_id');
            $table->smallInteger('anket_count_from')->unsigned();
            $table->smallInteger('anket_count_to')->unsigned();
            $table->smallInteger('limit_from')->unsigned();
            $table->smallInteger('limit_to')->unsigned();
            $table->timestamps();

            $table->foreign('type_id')->references('id')->on('profile_types')->onDelete('cascade')->onUpdate('cascade');
        });

        DB::table('operator_chat_limit_assigments')->insert([
            'type_id' => 1,
            'anket_count_from' => 1,
            'anket_count_to' => 2,
            'limit_from' => 1,
            'limit_to' => 3,
        ]);
        DB::table('operator_chat_limit_assigments')->insert([
            'type_id' => 2,
            'anket_count_from' => 2,
            'anket_count_to' => 3,
            'limit_from' => 1,
            'limit_to' => 3,
        ]);
        DB::table('operator_chat_limit_assigments')->insert([
            'type_id' => 3,
            'anket_count_from' => 2,
            'anket_count_to' => 6,
            'limit_from' => 1,
            'limit_to' => 3,
        ]);
        DB::table('operator_chat_limit_assigments')->insert([
            'type_id' => 4,
            'anket_count_from' => 1,
            'anket_count_to' => 5,
            'limit_from' => 1,
            'limit_to' => 3,
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_chat_limit_assigments');
    }
};
