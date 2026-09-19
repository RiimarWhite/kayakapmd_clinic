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
        Schema::create('dd_profile_menshist', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('p_TransNo', 21)->nullable();
            $table->string('en_caseno', 21)->nullable();
            $table->float('dMenarchePeriod')->unsigned()->nullable();
            $table->date('dLastMensPeriod')->nullable();
            $table->float('dPeriodDuration')->unsigned()->nullable();
            $table->float('dMensInterval')->unsigned()->nullable();
            $table->float('dPadsPerDay')->unsigned()->nullable();
            $table->float('dOnsetSexIc')->unsigned()->nullable();
            $table->string('dBirthCtrlMethod', 21)->nullable();
            $table->enum('dIsMenopause', ['Y', 'N', ''])->nullable();
            $table->float('dMenopauseAge')->unsigned()->nullable();
            $table->enum('dIsApplicable', ['Y', 'N'])->nullable();
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
        Schema::dropIfExists('dd_profile_menshist');
    }
};
