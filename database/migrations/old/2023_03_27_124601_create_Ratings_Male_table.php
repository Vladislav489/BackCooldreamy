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
        Schema::create('Ratings_Male', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->integer('User');
            $table->integer('ActionID')->comment('ID from Ratings_ActionsMale table');
            $table->dateTime('DateCreated')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Ratings_Male');
    }
};
