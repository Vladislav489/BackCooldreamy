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
        Schema::create('Letters_AttachedFiles', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->integer('LetterMessage');
            $table->string('FileUrl', 256)->default('');
            $table->string('FileType', 16)->default('');
            $table->string('Tags', 256)->default('');
            $table->tinyInteger('IsViewedByRecepient')->default(0);
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
        Schema::dropIfExists('Letters_AttachedFiles');
    }
};
