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
        Schema::create('arbitrator_comingid_urls', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('arbitrator_id')->nullable();
            $table->string('url_from',1500);
            $table->string('ip');
            $table->string('os');
            $table->string('lang_Browser');
            $table->string('type_divace');
            $table->string('type_Browser');
            $table->string('local');
            $table->string('tag');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arbitrator_comingid_url');
    }
};
