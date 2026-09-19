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
        Schema::create('walkinconsultation_answer', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('walkinconsuanswerrefno', 60)->nullable();
            $table->string('questionrefno', 60)->nullable();
            $table->longText('answer')->nullable();
            $table->string('pxconsultationrefno', 60)->nullable();
            $table->string('transactedby', 100)->nullable();
            $table->dateTime('transacteddate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('walkinconsultation_answer');
    }
};
