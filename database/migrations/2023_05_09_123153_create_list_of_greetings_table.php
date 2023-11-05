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
        Schema::create('list_of_greetings', function (Blueprint $table) {
            $table->id();
            $table->string('text', 128);
            $table->timestamps();
        });

        DB::table('list_of_greetings')->insert([
            'text' => 'Hey,',
        ]);
        DB::table('list_of_greetings')->insert([
            'text' => 'Hiya,',
        ]);
        DB::table('list_of_greetings')->insert([
            'text' => 'Greetings,',
        ]);
        DB::table('list_of_greetings')->insert([
            'text' => 'Salutations,',
        ]);
        DB::table('list_of_greetings')->insert([
            'text' => 'Howdy,',
        ]);
        DB::table('list_of_greetings')->insert([
            'text' => 'hi',
        ]);
        DB::table('list_of_greetings')->insert([
            'text' => 'Hi there, ',
        ]);
        DB::table('list_of_greetings')->insert([
            'text' => 'Hello',
        ]);
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_of_greetings');
    }
};
