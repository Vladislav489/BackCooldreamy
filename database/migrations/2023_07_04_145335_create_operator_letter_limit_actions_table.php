<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Operator\OperatorLetterLimitAction;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operator_letter_limit_actions', function (Blueprint $table) {
            $table->id();
            $table->double('limits')->default(0);
            $table->string('action')->nullable();
            $table->timestamps();
        });

        $openLimit = new OperatorLetterLimitAction();
        $openLimit->id = \App\Http\Controllers\API\V1\OperatorLetterLimitController::OPEN;
        $openLimit->limits = 1;
        $openLimit->save();

        $openLimit = new OperatorLetterLimitAction();
        $openLimit->id = \App\Http\Controllers\API\V1\OperatorLetterLimitController::SEND_MESSAGE;
        $openLimit->limits = 1;
        $openLimit->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_letter_limit_actions');
    }
};
