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
        Schema::create('dd_lib_package_type', function (Blueprint $table) {
            $table->string('PACKAGE_ID', 2)->nullable()->comment('PACKAGE ID NUMBER');
            $table->string('PACKAGE_DESC', 100)->nullable()->comment('PACKAGE DESCRIPTION');
            $table->string('PACKAGE_INFO', 400)->nullable()->comment('PACKAGE TYPE MORE DETAILS');
            $table->string('CPO_NO', 50)->nullable()->comment('CIRCULAR NUMBER OF PACKAGE TYPE');
            $table->decimal('LIB_SORT', 2, 0)->nullable()->comment('SORTING OF PACKAGE TYPE');
            $table->decimal('LIB_STATUS', 2, 0)->nullable()->comment('0 - INACTIVE; 1- ACTIVE');
            $table->date('DATE_CREATED')->nullable()->comment('DATE CREATED THE RECORD');
            $table->string('CREATED_BY', 20)->nullable()->comment('USER WHO INSERTED THE RECORD');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_lib_package_type');
    }
};
