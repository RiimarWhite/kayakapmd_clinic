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
        Schema::create('dw_lib_abdomen', function (Blueprint $table) {
            $table->string('abdomen_id', 3)->nullable()->comment('ABDOMEN DESCRIPTION ID');
            $table->string('abdomen_desc', 100)->nullable()->comment('ABDOMEN_DESCRIPTION');
            $table->integer('lib_stat')->nullable()->comment('STATUS(1=ACTIVE, 0=DEACTIVATED)');
            $table->dateTime('updated')->nullable()->comment('DATE WHEN THE RECORD WAS ADDED');
            $table->string('updatedby', 80)->nullable()->comment('REFERS TO THE USER WHO ADDED THE RECORD (LOGGED USER)');
            $table->integer('sort_no')->nullable()->comment('SORT NUMBER');
            $table->dateTime('date_deactivated')->nullable()->comment('DATE DEACTIVATED');
            $table->string('deactivatedby', 80)->nullable()->comment('REFERS TO THE USER WHO DEACTIVATED THE RECORD (LOGGED USER)');
            $table->tinyInteger('sys_usertype')->nullable()->comment('1 BOTH 2 INTERNAL 3 PHIC ONLY');
            $table->string('dw_clientcode', 12)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dw_lib_abdomen');
    }
};
