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
        Schema::create('dd_profile_pepert', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('p_TransNo', 21)->nullable();
            $table->string('en_caseno', 21)->nullable();
            $table->float('dSystolic')->unsigned();
            $table->float('dDiastolic')->unsigned();
            $table->float('dHr')->unsigned();
            $table->float('dRr')->unsigned();
            $table->float('dTemp')->unsigned()->comment('Temperature in Celsius');
            $table->float('dHeight')->unsigned()->comment('Height of Patient in cm ');
            $table->float('dWeight')->unsigned()->comment('Weight of Patient in kg');
            $table->float('dBMI')->unsigned();
            $table->string('dZScore', 10)->nullable();
            $table->string('dLeftVision', 12)->nullable();
            $table->string('dRightVision', 12)->nullable();
            $table->float('dLength')->unsigned()->nullable()->comment('Length of Patient in cm - for Pediatric
                Patient only age 0-24 Months');
            $table->float('dHeadCirc')->unsigned()->nullable();
            $table->float('dSkinfoldThickness')->unsigned()->nullable();
            $table->float('dWaist')->unsigned()->nullable();
            $table->float('dHip')->unsigned()->nullable();
            $table->float('dLimbs')->unsigned()->nullable();
            $table->float('dMidUpperArmCirc')->unsigned()->nullable();
            $table->enum('dReportStatus', ['V', 'U', 'F'])->nullable()->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->string('dDeficiencyRemarks', 2000)->nullable();
            $table->string('createdby', 80)->nullable();
            $table->dateTime('created')->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('updated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_profile_pepert');
    }
};
