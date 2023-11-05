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
        Schema::create('Letters', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->integer('FirstUser');
            $table->integer('SecondUser');
            $table->dateTime('DateCreated')->useCurrent();
            $table->tinyInteger('Disabled')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Letters');
    }
};
