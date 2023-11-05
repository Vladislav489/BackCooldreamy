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
        Schema::table('operator_letter_limits', function (Blueprint $table) {
            $table->unsignedBigInteger('letter_id')->nullable()->default(null);
        });

        foreach (\App\Models\Operator\OperatorLetterLimit::get() as $letterLimit) {
            $letter = \App\Models\Letter::query()->where(function ($builder) use ($letterLimit) {
                $builder->where('first_user_id', $letterLimit->man_id)
                    ->where('second_user_id', $letterLimit->girl_id);
            })->orWhere(function($builder) use ($letterLimit) {
                $builder->where('first_user_id', $letterLimit->girl_id)
                    ->where('second_user_id', $letterLimit->man_id);
            })->first();

            if ($letter) {
                $letterLimit->letter_id = $letter->id;
                $letterLimit->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operator_letter_limits', function (Blueprint $table) {
            $table->dropColumn('letter_id');
        });
    }
};
