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
        Schema::create('secretary_doctor', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('docrefno', 60)->nullable();
            $table->string('secrefno', 60)->nullable();
            $table->string('recordedby', 60)->nullable();
            $table->dateTime('recordeddate')->nullable();
            $table->tinyInteger('active')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secretary_doctor');
    }
};
