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
        Schema::create('operator_chat_limit_actions', function (Blueprint $table) {
            $table->id();
            $table->decimal('limits', 5, 3)->unsigned();
            $table->text('action', 128);
            $table->timestamps();
        });

        DB::table('operator_chat_limit_actions')->insert([
            'limits' => 5,
            'action' => 'FirstMessage',
        ]);
        DB::table('operator_chat_limit_actions')->insert([
            'limits' => 2,
            'action' => 'MessageInChat',
        ]);
        DB::table('operator_chat_limit_actions')->insert([
            'limits' => 1.5,
            'action' => 'SendWink',
        ]);
        DB::table('operator_chat_limit_actions')->insert([
            'limits' => 0.3,
            'action' => 'SendLike',
        ]);
        DB::table('operator_chat_limit_actions')->insert([
            'limits' => 0.3,
            'action' => 'OpenProfile',
        ]);
        DB::table('operator_chat_limit_actions')->insert([
            'limits' => 5,
            'action' => 'SendGift',
        ]);
        DB::table('operator_chat_limit_actions')->insert([
            'limits' => 0.2,
            'action' => 'ReadAce',
        ]);
        DB::table('operator_chat_limit_actions')->insert([
            'limits' => 2,
            'action' => 'StickerChat',
        ]);
        DB::table('operator_chat_limit_actions')->insert([
            'limits' => 2,
            'action' => 'ImageChat',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_chat_limit_actions');
    }
};
