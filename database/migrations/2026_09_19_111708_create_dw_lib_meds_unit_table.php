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
        Schema::create('dw_lib_meds_unit', function (Blueprint $table) {
            $table->string('unit_code', 5)->nullable();
            $table->string('unit_desc', 100)->nullable();
            $table->dateTime('updated')->nullable()->comment('DATE WHEN THE RECORD WAS ADDED');
            $table->string('updatedby', 80)->nullable()->comment('USER WHO ADDED THE RECORD');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dw_lib_meds_unit');
    }
};
