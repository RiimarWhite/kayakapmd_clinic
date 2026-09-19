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
        Schema::create('dd_lib_gen_survey', function (Blueprint $table) {
            $table->string('GENSURVEY_ID', 1)->nullable()->comment('GENERAL SURVEY ID');
            $table->string('GENSURVEY_DESC', 100)->nullable()->comment('GENERAL SURVEY DESCRIPTION');
            $table->date('DATE_ADDED')->nullable();
            $table->string('ADDED_BY', 20)->nullable();
            $table->integer('LIB_STAT')->nullable()->comment('LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED)');
            $table->date('DATE_DEACTIVATED')->nullable()->comment('DATE WHEN DEACTIVATED');
            $table->string('DEACTIVATED_BY', 20)->nullable()->comment('USER WHO DEACTIVATED THE RECORD');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_lib_gen_survey');
    }
};
