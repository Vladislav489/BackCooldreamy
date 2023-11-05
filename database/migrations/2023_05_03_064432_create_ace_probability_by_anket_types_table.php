<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ace_probability_by_anket_types', function (Blueprint $table) {
            $table->id();
            $table->string('type', 128);
            $table->smallInteger('type_id');
            $table->decimal('probability', 8, 3);
            $table->timestamps();
        });

        DB::table('ace_probability_by_anket_types')->insert([
            'type' => 'top',
            'type_id' => 1,
            'probability' => '0.125'
        ]);
        DB::table('ace_probability_by_anket_types')->insert([
            'type' => '18+',
            'type_id' => 2,
            'probability' => '0.125'
        ]);
        DB::table('ace_probability_by_anket_types')->insert([
            'type' => 'prem',
            'type_id' => 3,
            'probability' => '0.25'
        ]);
        DB::table('ace_probability_by_anket_types')->insert([
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
        Schema::dropIfExists('ace_probability_by_anket_types');
    }
};
