<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{


    public function up(): void
    {
        Schema::create('limit_probability_by_anket_types', function (Blueprint $table) {
            $table->id();
            $table->string('type', 128);
            $table->smallInteger('type_id');
            $table->tinyInteger('age_from')->nullable();
            $table->tinyInteger('age_to')->nullable();
            $table->decimal('probability', 8, 3);
            $table->timestamps();
        });

        DB::table('limit_probability_by_anket_types')->insert([
            'type' => 'top',
            'type_id' => 1,
            'probability' => '0.125'
        ]);
        DB::table('limit_probability_by_anket_types')->insert([
            'type' => '18+',
            'type_id' => 2,
            'probability' => '0.125'
        ]);
        DB::table('limit_probability_by_anket_types')->insert([
            'type' => 'prem',
            'type_id' => 3,
            'probability' => '0.25'
        ]);
        DB::table('limit_probability_by_anket_types')->insert([
            'type' => 'standart',
            'type_id' => 4,
            'probability' => '0.5'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limit_probability_by_anket_types');
    }
};
