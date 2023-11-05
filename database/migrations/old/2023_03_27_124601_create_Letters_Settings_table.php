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
        Schema::create('Letters_Settings', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->integer('ReadLetterPrice');
            $table->integer('SendLetterPrice');
            $table->integer('ViewPhotoPrice');
            $table->integer('ViewVideoPrice');
            $table->integer('AttachFilePrice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Letters_Settings');
    }
};
