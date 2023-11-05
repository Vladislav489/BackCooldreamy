<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_prices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price');
            $table->integer('disabled');
            $table->timestamps();
        });

        DB::table('service_prices')->insert([
            'name' => 'Отправить сообщение',
            'price' => 5,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Отправить письмо',
            'price' => 15,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Отправка фото в чате',
            'price' => 5,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Отправка видео в чате',
            'price' => 15,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Посмотреть фото в чате',
            'price' => 5,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Посмотреть видео в чате',
            'price' => 15,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Отправить стикер чат/стрим',
            'price' => 5,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Открыть письмо от девушки',
            'price' => 15,
            'disabled' => 0
        ]);

        DB::table('service_prices')->insert([
            'name' => 'Посмотреть фото в письме',
            'price' => 15,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Посмотреть видео в пиьме',
            'price' => 5,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Статус инкогнито навсегда',
            'price' => 15,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Активность в фотоленте',
            'price' => 5,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Отправить подарок в чате',
            'price' => 15,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Отправить подарок в письме',
            'price' => 15,
            'disabled' => 0
        ]);
        DB::table('service_prices')->insert([
            'name' => 'Отправить стикер в письме',
            'price' => 15,
            'disabled' => 0
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_prices');
    }
};
