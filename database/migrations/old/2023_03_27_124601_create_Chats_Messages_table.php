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
        Schema::create('Chats_Messages', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->integer('ChatID');
            $table->integer('SenderUser');
            $table->string('TextMessage', 1000)->default('');
            $table->tinyInteger('IsReadByRecepient')->default(0);
            $table->dateTime('CreatedDate')->useCurrent();
            $table->integer('Timer')->default(0);
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
        Schema::dropIfExists('Chats_Messages');
    }
};
