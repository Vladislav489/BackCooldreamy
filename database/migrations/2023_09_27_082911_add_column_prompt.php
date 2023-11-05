<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void{
        Schema::table('prompt_careers', function (Blueprint $table) {
            $table->string('icon',400)->nullable();
        });
        Schema::table('prompt_chat_messages', function (Blueprint $table) {
            $table->string('icon',400)->nullable();
        });
        Schema::table('prompt_finance_states', function (Blueprint $table) {
            $table->string('icon',400)->nullable();
        });
        Schema::table('prompt_interests', function (Blueprint $table) {
            $table->string('icon',400)->nullable();
        });
        Schema::table('prompt_relationships', function (Blueprint $table) {
            $table->string('icon',400)->nullable();
        });
        Schema::table('prompt_reports', function (Blueprint $table) {
            $table->string('icon',400)->nullable();
        });
        Schema::table('prompt_targets', function (Blueprint $table) {
            $table->string('icon',400)->nullable();
        });
        Schema::table('prompt_sources', function (Blueprint $table) {
            $table->string('icon',400)->nullable();
        });
        Schema::table('prompt_want_kids', function (Blueprint $table) {
            $table->string('icon',400)->nullable();
        });

    }
    /**
     * Reverse the migrations.
     */
    public function down(): void{
        Schema::table('prompt_careers', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
        Schema::table('prompt_chat_messages', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
        Schema::table('prompt_finance_states', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
        Schema::table('prompt_interests', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
        Schema::table('prompt_relationships', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
        Schema::table('prompt_reports', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
        Schema::table('prompt_targets', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
        Schema::table('prompt_sources', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
        Schema::table('prompt_want_kids', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
