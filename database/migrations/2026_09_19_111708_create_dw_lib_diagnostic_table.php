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
        Schema::create('dw_lib_diagnostic', function (Blueprint $table) {
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('diagnostic_id', 3)->nullable()->comment('DIAGNOSTIC DESCRIPTION ID');
            $table->enum('item_grouping', ['DIAGNOSTIC', 'IMAGING', 'PROCEDURES', 'OTHERS'])->nullable();
            $table->string('diagnostic_desc', 100)->nullable()->comment('DIAGNOSTIC DESCRIPTION');
            $table->integer('lib_stat')->nullable()->comment('LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED)');
            $table->dateTime('updated')->nullable()->comment('DATE WHEN THE RECORD WAS ADDED');
            $table->string('updatedby', 80)->nullable()->comment('USER WHO ADDED THE RECORD');
            $table->integer('sort_no')->nullable()->comment('SORT NUMBER');
            $table->dateTime('date_deactivated')->nullable()->comment('DATE WHEN DEACTIVATED');
            $table->string('deactivatedby', 80)->nullable()->comment('USER WHO DEACTIVATED THE RECORD');
            $table->tinyInteger('sys_usertype')->nullable()->comment('1 BOTH 2 INTERNAL 3 PHIC ONLY');
            $table->double('diag_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dw_lib_diagnostic');
    }
};
