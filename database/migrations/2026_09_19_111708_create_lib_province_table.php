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
        Schema::create('lib_province', function (Blueprint $table) {
            $table->string('PROCODE', 2)->nullable()->comment('PRO CODE OFFICE CODE');
            $table->string('PROVINCE', 2)->nullable()->comment('PROVINCE OFFICE CODE');
            $table->string('PROV_NAME', 60)->nullable()->comment('MUNICIPALITY OFFICE CODE');
            $table->string('AREACODE', 1)->nullable()->comment('BARANGAY OFFICE CODE');
            $table->string('LHIO', 5)->nullable()->comment('BARANGAY OFFICE CODE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lib_province');
    }
};
