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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false);
        });

        $role = new \Spatie\Permission\Models\Role();
        $role->id = 3;
        $role->name = 'acquiring';
        $role->guard_name = 'web';
        $role->save();

        $moderator = \App\Models\User::factory([
            'email' => 'acquiring-dev@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('secret')
        ])->create();
        $moderator->roles()->sync(3);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_blocked');
        });
    }
};
