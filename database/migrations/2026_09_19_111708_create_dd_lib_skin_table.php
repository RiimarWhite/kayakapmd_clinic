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
        Schema::create('dd_lib_skin', function (Blueprint $table) {
            $table->string('SKIN_ID', 3)->nullable()->comment('SKIN DESCRIPTION ID');
            $table->string('SKIN_DESC', 100)->nullable()->comment('SKIN DESCRIPTION');
            $table->dateTime('DATE_ADDED')->nullable()->comment('DATE WHEN THE RECORD WAS ADDED');
            $table->string('ADDED_BY', 20)->nullable()->comment('REFERS TO THE USER WHO ADDED THE RECORD (LOGGED USER)');
            $table->integer('SORT_NO')->nullable()->comment('SORT NUMBER');
            $table->integer('LIB_STAT')->nullable()->comment('STATUS(1=ACTIVE, 0=DEACTIVATED)');
            $table->dateTime('DATE_DEACTIVATED')->nullable()->comment('DATE DEACTIVATED');
            $table->string('DEACTIVATED_BY', 20)->nullable()->comment('REFERS TO THE USER WHO DEACTIVATED THE RECORD (LOGGED USER)');
            $table->tinyInteger('sys_usertype')->nullable()->comment('1 BOTH 2 INTERNAL 3 PHIC ONLY');
            $table->string('dw_clientcode', 12)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_lib_skin');
    }
};
