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
        Schema::create('dd_lib_unit_references', function (Blueprint $table) {
            $table->string('FIELD_NAME', 20)->nullable()->comment('FIELD NAME');
            $table->string('FIELD_CODE', 5)->nullable()->comment('FIELD CODE');
            $table->string('FIELD_DESC', 150)->nullable()->comment('FIELD DESCRIPTION');
            $table->date('DATE_ADDED')->nullable()->comment('DATE WHEN ADDED');
            $table->string('ADDED_BY', 20)->nullable()->comment('USER WHO ADD THE RECORD');
            $table->integer('SORT_NO')->nullable()->comment('SORT NUMBER');
            $table->integer('LIB_STAT')->nullable()->comment('LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED)');
            $table->dateTime('DATE_DEACTIVATED')->nullable()->comment('DATE WHEN DEACTIVATED');
            $table->string('DEACTIVATED_BY', 20)->nullable()->comment('USER WHO DEACTIVATED THE RECORD');
            $table->string('dw_clientcode', 12)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_lib_unit_references');
    }
};
