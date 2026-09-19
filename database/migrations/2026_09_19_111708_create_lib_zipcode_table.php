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
        Schema::create('lib_zipcode', function (Blueprint $table) {
            $table->string('PROCODE', 2)->nullable()->comment('PRO CODE OFFICE CODE');
            $table->string('PROVINCE', 2)->nullable()->comment('PROVINCE OFFICE CODE');
            $table->string('MUNICIPALITY', 2)->nullable()->comment('MUNICIPALITY OFFICE CODE');
            $table->string('ZIP_CODE', 4)->nullable()->comment('BARANGAY OFFICE CODE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lib_zipcode');
    }
};
