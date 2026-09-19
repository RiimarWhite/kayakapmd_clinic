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
        Schema::create('lib_region', function (Blueprint $table) {
            $table->decimal('REGION_CODE', 10, 0)->nullable()->comment('REGION ADDRESS CODE');
            $table->string('REGION_NAME', 50)->nullable()->comment('REGION NAME');
            $table->decimal('PRO_CODE', 10, 0)->nullable()->comment('PRO CODE');
            $table->decimal('PRO_SORT', 2, 0)->nullable()->comment('SORTING OF PROCODE');
            $table->string('LHIO', 2)->nullable()->comment('LHIO CODE ADDRESS');
            $table->string('REGION_ID', 10)->nullable()->comment('REGION ID');
            $table->string('REGION_DESC', 50)->nullable()->comment('REGION DESCRIPTION');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lib_region');
    }
};
