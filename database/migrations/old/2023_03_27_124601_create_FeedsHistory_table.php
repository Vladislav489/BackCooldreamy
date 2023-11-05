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
        Schema::create('FeedsHistory', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->integer('FromUser');
            $table->integer('ToUser');
            $table->tinyInteger('IsSkipped')->default(0);
            $table->tinyInteger('IsLiked')->default(0);
            $table->dateTime('CreatedDate')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FeedsHistory');
    }
};
