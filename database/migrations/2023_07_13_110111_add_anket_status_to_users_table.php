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
            $table->string('anket_status')->nullable()->default(\App\Enum\Anket\AnketStatusEnum::NEW);
        });
        $role = new \Spatie\Permission\Models\Role();
        $role->id = 5;
        $role->name = 'bank-admin';
        $role->guard_name = 'web';
        $role->save();

        $moderator = \App\Models\User::factory([
            'email' => 'bank-admin-dev@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('secret')
        ])->create();

        $moderator->roles()->sync(5);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('anket_status');
        });
    }
};
