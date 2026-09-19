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
            $table->unsignedBigInteger('id')->nullable();
            $table->string('medicine_refno')->nullable();
            $table->string('medicine_name')->nullable();
            $table->string('philhealth_refno')->nullable();
            $table->double('rate')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
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
