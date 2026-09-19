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
        Schema::create('lib_barangay', function (Blueprint $table) {
            $table->string('PROCODE', 255)->nullable();
            $table->string('PROVINCE', 255)->nullable();
            $table->string('MUNICIPALITY', 255)->nullable();
            $table->string('BARANGAY', 255)->nullable();
            $table->string('BRGY_NAME', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lib_barangay');
    }
};
