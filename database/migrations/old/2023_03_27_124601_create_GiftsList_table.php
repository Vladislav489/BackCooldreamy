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
        Schema::create('GiftsList', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->string('Category', 64)->default('');
            $table->string('Name', 64)->default('');
            $table->integer('Credits');
            $table->string('PictureUrl', 256)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('GiftsList');
    }
};
