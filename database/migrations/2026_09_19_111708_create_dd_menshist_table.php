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
        Schema::create('dd_menshist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('pMenarchePeriod')->nullable();
            $table->date('pLastMensPeriod')->nullable();
            $table->unsignedInteger('pPeriodDuration')->nullable();
            $table->unsignedInteger('pMensInterval')->nullable();
            $table->unsignedInteger('pPadsPerDay')->nullable();
            $table->unsignedInteger('pOnsetSexIc')->nullable();
            $table->string('pBirthCtrlMethod', 21)->nullable();
            $table->enum('pIsMenopause', ['Y', 'N'])->nullable();
            $table->unsignedInteger('pMenopauseAge')->nullable();
            $table->enum('pIsApplicable', ['Y', 'N']);
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
        Schema::dropIfExists('dd_menshist');
    }
};
