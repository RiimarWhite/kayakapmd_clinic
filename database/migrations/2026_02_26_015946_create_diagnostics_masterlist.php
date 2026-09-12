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
        Schema::create('diagnostics_masterlist', function (Blueprint $table) {
            $table->id();
            $table->string('diagnosticrefno')->unique();
            $table->string('diagnostic_name');
            $table->string('diagnostic_catg');
            $table->datetimes();
        });

        Schema::create('diagnostic_category', function (Blueprint $table) {
            $table->id();
            $table->string('category_refno')->unique();
            $table->string('category_name');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnostics_masterlist');
        Schema::dropIfExists('diagnostic_category');
    }
};
