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
        Schema::create('dd_pcb', function (Blueprint $table) {
            $table->id();
            $table->string('pUsername', 30);
            $table->string('pPassword', 30);
            $table->string('pHciAccreNo', 9);
            $table->string('pPMCCNo', 6);
            $table->integer('pEnlistTotalCnt');
            $table->integer('pProfileTotalCnt');
            $table->integer('pSoapTotalCnt');
            $table->string('pCertificationId', 21);
            $table->string('pHciTransmittalNumber', 21);
            $table->timestamps();
        });

        Schema::create('dd_medhist', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
            
            $table->id();
            $table->string('pMdiseaseCode', 100)->nullable()->index();
            // $table->foreign('pMdiseaseCode')->references('mdisease_code')->on('lib_mdisease')->restrictOnDelete();
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_mhspecific', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->id();
            $table->string('pMdiseaseCode', 100)->nullable()->index();
            // $table->foreign('pMdiseaseCode')->references('mdisease_code')->on('lib_mdisease')->restrictOnDelete();
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_surghist', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->id();
            $table->text('pSurgDesc')->nullable();
            $table->date('pSurgDate')->nullable();
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_famhist', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->id();
            $table->string('pMdiseaseCode', 100)->nullable()->index();
            // $table->foreign('pMdiseaseCode')->references('mdisease_code')->on('lib_mdisease')->restrictOnDelete();
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_fhspecific', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->id();
            $table->string('pMdiseaseCode', 100)->nullable()->index();
            // $table->foreign('pMdiseaseCode')->references('mdisease_code')->on('lib_mdisease')->restrictOnDelete();
            $table->text('pSpecificDesc')->nullable();
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_sochist', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->id();
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

        Schema::create('dd_immunization', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
            
            $table->id();
            $table->string('pChildImmcode', 100)->nullable()->index();
            // $table->foreign('pChildImmcode')->references('immchild_code')->on('lib_immchild')->restrictOnDelete();
            $table->string('pYoungwImmcode', 100)->nullable()->index();
            // $table->foreign('pYoungwImmcode')->references('immyoungw_code')->on('lib_immyoungw')->restrictOnDelete();
            $table->string('pPregwImmcode', 100)->nullable()->index();
            // $table->foreign('pPregwImmcode')->references('immpregw_code')->on('lib_immpregw')->restrictOnDelete();
            $table->string('pElderlyImmcode', 100)->nullable()->index();
            // $table->foreign('pElderlyImmcode')->references('immelderly_code')->on('lib_immelderly')->restrictOnDelete();
            $table->text('pOtherImm')->nullable();
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
        Schema::dropIfExists('dd_pcb');
        Schema::dropIfExists('dd_medhist');
        Schema::dropIfExists('dd_mhspecific');
        Schema::dropIfExists('dd_surghist');
        Schema::dropIfExists('dd_famhist');
        Schema::dropIfExists('dd_fhspecific');
        Schema::dropIfExists('dd_sochist');
        Schema::dropIfExists('dd_immunization');
    }
};
