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
        Schema::create('dd_pepert', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('pSystolic');
            $table->unsignedInteger('pDiastolic');
            $table->unsignedInteger('pHr');
            $table->unsignedInteger('pRr');
            $table->unsignedInteger('pTemp')->comment('Temperature in Celsius');
            $table->unsignedInteger('pHeight')->comment('Height of Patient in cm ');
            $table->unsignedInteger('pWeight')->comment('Weight of Patient in kg');
            $table->unsignedInteger('pBMI');
            $table->string('pZScore', 10)->nullable();
            $table->string('pLeftVision', 12)->nullable();
            $table->string('pRightVision', 12)->nullable();
            $table->unsignedInteger('pLength')->nullable()->comment('Length of Patient in cm - for Pediatric
                Patient only age 0-24 Months');
            $table->unsignedInteger('pHeadCirc')->nullable();
            $table->unsignedInteger('pSkinfoldThickness')->nullable();
            $table->unsignedInteger('pWaist')->nullable();
            $table->unsignedInteger('pHip')->nullable();
            $table->unsignedInteger('pLimbs')->nullable();
            $table->unsignedInteger('pMidUpperArmCirc')->nullable();
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U')->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_pepert');
    }
};
