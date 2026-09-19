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
        Schema::create('dw_lib_medicine', function (Blueprint $table) {
            $table->string('DRUG_CODE', 30)->nullable();
            $table->string('DRUG_DESC', 100)->nullable();
            $table->string('GEN_CODE', 5)->nullable();
            $table->string('SALT_CODE', 5)->nullable();
            $table->string('FORM_CODE', 5)->nullable();
            $table->string('STRENGTH_CODE', 5)->nullable();
            $table->string('UNIT_CODE', 5)->nullable();
            $table->string('PACKAGE_CODE', 5)->nullable();
            $table->string('CATEGORY', 50)->nullable()->comment('CATEGORY DESCRIPTION - KONSULTA');
            $table->dateTime('updated')->nullable()->comment('DATE WHEN THE RECORD WAS ADDED');
            $table->string('updatedby', 80)->nullable()->comment('USER WHO ADDED THE RECORD');
            $table->tinyInteger('sys_usertype')->nullable()->comment('1 BOTH 2 INTERNAL 3 PHIC ONLY');
            $table->string('dw_clientcode', 12)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dw_lib_medicine');
    }
};
