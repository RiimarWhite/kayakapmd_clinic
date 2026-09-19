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
        Schema::create('pxrxdocuments', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('rxreferenceno', 50)->nullable();
            $table->string('consultationrefno', 50)->nullable();
            $table->string('medicinecode', 100)->nullable();
            $table->string('medicinename', 100)->nullable();
            $table->string('medicinedosage', 100)->nullable();
            $table->string('medicineduration', 50)->nullable();
            $table->string('medicinequantity', 50)->nullable();
            $table->dateTime('createddate')->nullable();
            $table->string('createdby', 50)->nullable();
            $table->string('docrefno', 50)->nullable();
            $table->dateTime('sentdate')->nullable();
            $table->string('sentby', 50)->nullable();
            $table->string('templatename', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pxrxdocuments');
    }
};
