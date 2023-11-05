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
        Schema::table('operator_chat_limits', function (Blueprint $table) {
            $table->unsignedBigInteger('chat_id')->nullable()->default(null);
        });

        foreach (\App\Models\OperatorChatLimit::get() as $chatLimit) {
            $chat = \App\Models\Chat::query()->where(function ($builder) use ($chatLimit) {
                $builder->where('first_user_id', $chatLimit->man_id)
                    ->where('second_user_id', $chatLimit->girl_id);
            })->orWhere(function($builder) use ($chatLimit) {
                $builder->where('first_user_id', $chatLimit->girl_id)
                    ->where('second_user_id', $chatLimit->man_id);
            })->first();

            if ($chat) {
                $chatLimit->chat_id = $chat->id;
                $chatLimit->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operator_chat_limits', function (Blueprint $table) {
            $table->dropColumn('chat_id');
        });
    }
};
