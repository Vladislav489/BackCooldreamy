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
        Schema::table('managers_reminders', function (Blueprint $table) {
            $table->index('from_manager_id', 'mr_from_manager_id_index');
            $table->index('from_user_id', 'mr_from_user_id_index');
            $table->index('to_user_id', 'mr_to_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('managers_reminders', function (Blueprint $table) {
            $table->dropIndex('mr_from_manager_id_index');
            $table->dropIndex('mr_from_user_id_index');
            $table->dropIndex('mr_to_user_id_index');
        });
    }
};
