<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Prompts_Interests', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->string('IconUrl', 256)->default('');
            $table->string('Text', 126)->default('');
            $table->string('Gender', 16)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Prompts_Interests');
    }
};
