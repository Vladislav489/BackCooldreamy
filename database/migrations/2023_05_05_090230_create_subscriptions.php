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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->integer("user_id");
            $table->integer("service_id");
            $table->datetime("period_start");
            $table->datetime("period_end");
            $table->integer("days");
            $table->timestamps();
        });

        DB::table('subscriptions')->insert([
            'user_id' => 3282,
            'service_id' => 2,
            'period_start' => "2023-05-05 10:00:00",
            'period_end' => "2023-06-26 10:00:00",
            'days' => 15
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
