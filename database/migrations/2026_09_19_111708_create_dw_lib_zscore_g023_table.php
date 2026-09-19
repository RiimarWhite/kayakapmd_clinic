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
        Schema::create('dw_lib_zscore_g023', function (Blueprint $table) {
            $table->string('LIB_CODE', 4)->nullable()->comment('Library Code');
            $table->string('LENGTH', 10)->nullable()->comment('LENGTH IN CM');
            $table->string('WEIGHT', 10)->nullable()->comment('WEIGHT IN KG');
            $table->string('RESULT_CODE', 10)->nullable()->comment('Result Code');
            $table->string('RESULT_DESC', 50)->nullable()->comment('Result Desc');
            $table->tinyInteger('sys_usertype')->nullable()->comment('1 BOTH 2 INTERNAL 3 PHIC ONLY');
            $table->string('dw_clientcode', 12)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dw_lib_zscore_g023');
    }
};
