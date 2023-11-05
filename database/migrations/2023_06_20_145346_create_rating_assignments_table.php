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
        Schema::create('rating_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->unsignedDecimal('limit', 8, 4);
            $table->timestamps();
        });

        resolve(\Database\Seeders\RatingAssignmentTableSeeder::class)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_assignments');
    }
};
