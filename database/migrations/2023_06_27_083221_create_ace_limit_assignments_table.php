<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Ace\AceLimitAssignment;
use App\Enum\Ace\AceLimitAssignmentEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ace_limit_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('limit');
            $table->unsignedBigInteger('second_from');
            $table->unsignedBigInteger('second_to');
            $table->timestamps();
        });

        foreach (AceLimitAssignmentEnum::getList() as $item) {
            AceLimitAssignment::create([
                'limit' => $item,
                'second_from' => AceLimitAssignmentEnum::getFrom($item),
                'second_to' => AceLimitAssignmentEnum::getTo($item),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ace_limit_assignments');
    }
};
