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
        Schema::create('anket_files', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('type');
            $table->unsignedBigInteger('anket_id');
            $table->foreign('anket_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });

        $role = new \Spatie\Permission\Models\Role();
        $role->id = 4;
        $role->name = 'management';
        $role->guard_name = 'web';
        $role->save();

        $moderator = \App\Models\User::factory([
            'email' => 'management-dev@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('secret')
        ])->create();

        $moderator->roles()->sync(4);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anket_files');
    }
};
