<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('UserData', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->string('Token', 128)->default('');
            $table->string('Email', 256)->default('');
            $table->tinyInteger('IsConfirmedEmail')->default(0);
            $table->string('Password', 512)->default('')->comment('Hash SHA-3');
            $table->string('Name', 256)->default('');
            $table->string('State', 256)->default('');
            $table->string('Country', 256)->default('');
            $table->date('Birthday')->nullable();
            $table->string('Gender', 16)->default('')->comment('Male or Female');
            $table->string('Q_Targets', 64)->default('')->comment('IDs from Promts_Targets table (1,2,3)');
            $table->string('Q_Interests', 64)->default('')->comment('IDs from Prompts_Interests table (1,2,3)');
            $table->integer('Q_FinanceState')->default(0)->comment('ID from Prompts_FinanceStates table');
            $table->integer('Q_Source')->default(0)->comment('ID from Prompts_Sources table');
            $table->integer('Q_WantKids')->default(0)->comment('ID from Prompts_WantKids table');
            $table->integer('Q_Relationship')->default(0)->comment('ID from Prompts_Relationships table');
            $table->integer('Q_Career')->default(0)->comment('ID from Prompts_Careers table');
            $table->string('AvatarUrl', 256)->default('');
            $table->tinyInteger('IsNewAvatar')->default(0);
            $table->tinyInteger('IsConfirmedUser')->default(0);
            $table->string('AboutSelf', 512)->default('');
            $table->dateTime('LastOnline')->useCurrent();
            $table->tinyInteger('IsPremium')->default(0);
            $table->dateTime('PremiumExpire')->nullable();
            $table->tinyInteger('IsInTop')->default(0);
            $table->dateTime('TopExpire')->nullable();
            $table->integer('Credits')->default(5);
            $table->string('Tags', 256)->default('')->comment('#18+');
            $table->enum('Type', ['18+', 'TOP'])->nullable();
            $table->dateTime('CreatedDate')->useCurrent();
            $table->tinyInteger('Disabled')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('UserData');
    }
};
