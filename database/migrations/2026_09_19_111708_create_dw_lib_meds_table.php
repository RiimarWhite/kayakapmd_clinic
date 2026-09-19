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
        Schema::create('dw_lib_meds', function (Blueprint $table) {
            $table->string('DRUG_CODE', 100)->nullable();
            $table->string('DRUG_DESCRIPTION', 50)->nullable();
            $table->string('STOCK_DOSAGE', 20)->nullable();
            $table->string('PREPARATION', 150)->nullable();
            $table->string('ADD_DESCRIPTION', 300)->nullable();
            $table->tinyInteger('sys_usertype')->nullable()->comment('1 BOTH 2 INTERNAL 3 PHIC ONLY');
            $table->string('dw_clientcode', 12)->nullable();
            $table->tinyInteger('yakap_essential')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dw_lib_meds');
    }
};
