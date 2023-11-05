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
        Schema::create('FavoriteProfiles', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->integer('User');
            $table->integer('FavoriteUser');
            $table->dateTime('Date')->useCurrent();
            $table->integer('Disabled')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FavoriteProfiles');
    }
};
