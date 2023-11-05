<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions_list', function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->integer("hours");
            $table->integer("days");
            $table->integer("months");
            $table->string("services_list");
            $table->timestamps();
        });

        DB::table('subscriptions_list')->insert([
            'title' => "подписка на 1 час",
            'hours' => 1,
            'days' => 0,
            'months' => 0,
            'services_list' => ""
        ]);

        DB::table('subscriptions_list')->insert([
            'title' => "подписка на 24 часа",
            'hours' => 0,
            'days' => 1,
            'months' => 0,
            'services_list' => "1,2"
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions_list');
    }
};
