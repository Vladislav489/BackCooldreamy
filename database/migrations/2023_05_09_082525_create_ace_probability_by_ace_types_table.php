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
        Schema::create('ace_probability_by_ace_types', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('ice_type')->unsigned();
            $table->smallInteger('profile_type_id');
            $table->decimal('probability', 6, 3);
            $table->string('comment', 128)->nullable();
            $table->timestamps();

            $table->foreign('profile_type_id')->references('id')->on('profile_types')->onDelete('cascade')->onUpdate('cascade');
        });

        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 1,
            'profile_type_id' => 1,
            'probability' => 0.15,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 2,
            'profile_type_id' => 1,
            'probability' => 0.15,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 3,
            'profile_type_id' => 1,
            'probability' => 0.15,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 4,
            'profile_type_id' => 1,
            'probability' => 0.15,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 5,
            'profile_type_id' => 1,
            'probability' => 0.25,
            'comment' => 'interest',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 6,
            'profile_type_id' => 1,
            'probability' => 0.15,
            'comment' => 'target',
        ]);


        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 1,
            'profile_type_id' => 2,
            'probability' => 0,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 2,
            'profile_type_id' => 2,
            'probability' => 0.25,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 3,
            'profile_type_id' => 2,
            'probability' => 0,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 4,
            'profile_type_id' => 2,
            'probability' => 0.5,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 5,
            'profile_type_id' => 2,
            'probability' => 0,
            'comment' => 'interest',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 6,
            'profile_type_id' => 2,
            'probability' => 0.25,
            'comment' => 'target',
        ]);


        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 1,
            'profile_type_id' => 3,
            'probability' => 0.15,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 2,
            'profile_type_id' => 3,
            'probability' => 0.15,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 3,
            'profile_type_id' => 3,
            'probability' => 0.15,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 4,
            'profile_type_id' => 3,
            'probability' => 0.15,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 5,
            'profile_type_id' => 3,
            'probability' => 0.25,
            'comment' => 'interest',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 6,
            'profile_type_id' => 3,
            'probability' => 0.15,
            'comment' => 'target',
        ]);


        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 1,
            'profile_type_id' => 4,
            'probability' => 0.15,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 2,
            'profile_type_id' => 4,
            'probability' => 0.15,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 3,
            'profile_type_id' => 4,
            'probability' => 0.15,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 4,
            'profile_type_id' => 4,
            'probability' => 0.15,
            'comment' => '',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 5,
            'profile_type_id' => 4,
            'probability' => 0.25,
            'comment' => 'interest',
        ]);
        DB::table('ace_probability_by_ace_types')->insert([
            'ice_type' => 6,
            'profile_type_id' => 4,
            'probability' => 0.15,
            'comment' => 'target',
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ace_probability_by_ace_types');
    }
};
