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
        Schema::create('medicine_masterlist', function (Blueprint $table) {
            $table->id();
            $table->string('medicine_refno')->unique();
            $table->string('medicine_name');
            $table->string('philhealth_refno')->nullable();
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_masterlist');
    }
};
