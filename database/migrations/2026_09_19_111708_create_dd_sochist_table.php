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
        Schema::create('dd_sochist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('pIsSmoker', ['Y', 'N', 'X']);
            $table->integer('pNoCigpk')->nullable();
            $table->enum('pIsADrinker', ['Y', 'N', 'X']);
            $table->integer('pNoBottles')->nullable();
            $table->enum('pIllDrugUser', ['Y', 'N']);
            $table->enum('pIsSexuallyActive', ['Y', 'N']);
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_sochist');
    }
};
