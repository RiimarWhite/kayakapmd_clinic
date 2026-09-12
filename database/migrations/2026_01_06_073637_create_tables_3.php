<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dd_icd', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->id();
            
            $table->string('pIcdCode', 255);
            // $table->foreign('pIcdCode')->references('icd_code')->on('lib_icd')->restrictOnDelete();
            
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_diagnostic', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->id();
            
            $table->string('pDiagnosticId', 11)->nullable();
            // $table->foreign('pDiagnosticId')->references('diagnostic_id')->on(('lib_diagnostic'))->restrictOnDelete();
            
            $table->text('pOthRemarks')->nullable();
            $table->enum('pIsPhysicianRecommend', ['Y', 'N', 'X']);
            $table->enum('pPatientRemarks', ['RQ','RF', 'XX']);
            $table->enum('pReportStatus',['V', 'U', 'F'])->default('U');
            $table->timestamps();
        });

        Schema::create('dd_management', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->id();
            
            $table->string('pManagementId', 100)->nullable();
            // $table->foreign('pManagementId')->references('management_id')->on('lib_management')->restrictOnDelete();
            
            $table->text('pOthRemarks')->nullable();
            $table->enum('pIsPhysicianRecommended', ['Y', 'N', 'X']);
            $table->enum('pPatientRemarks', ['RQ', 'RF', 'XX']);
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_advice', function (Blueprint $table) {
            $table->id();
            $table->text('pRemarks')->nullable();
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_medicine', function (Blueprint $table) {
            $table->string('pHciCaseNo', 21);
            $table->foreign('pHciCaseNo')->references('pHciCaseNo')->on('dd_enlistment')->restrictOnDelete();
            
            $table->string('pHciTransNo', 21);
            $table->foreign('pHciTransNo')->references('pHciTransNo')->on('dd_soap_consultation')->restrictOnDelete();
            
            $table->string('pCategory')->nullable()->index();
            // $table->foreign('pCategory')->references('category')->on('lib_medicine')->restrictOnDelete();
            
            $table->string('pDrugCode')->nullable()->index();
            // $table->foreign('pDrugCode')->references('drug_code')->on('lib_medicine')->restrictOnDelete();
            
            $table->string('pGenericCode')->nullable()->index();
            // $table->foreign('pGenericCode')->references('generic_code')->on('lib_medicine_generic')->restrictOnDelete();
            
            $table->string('pSaltCode')->nullable()->index();
            // $table->foreign('pSaltCode')->references('salt_code')->on('lib_medicine_salt')->restrictOnDelete();
            
            $table->string('pStrengthCode')->nullable()->index();
            // $table->foreign('pStrengthCode')->references('strength_code')->on('lib_medicine_strength')->restrictOnDelete();
            
            $table->string('pFormCode')->nullable()->index();
            // $table->foreign('pFormCode')->references('form_code')->on('lib_medicine_form')->restrictOnDelete();
            
            $table->string('pUnitCode')->nullable()->index();
            // $table->foreign('pUnitCode')->references('unit_code')->on('lib_medicine_unit')->restrictOnDelete();
            
            $table->string('pPackageCode')->nullable()->index();
            // $table->foreign('pPackageCode')->references('package_code')->on('lib_medicine_package')->restrictOnDelete();
            
            $table->text('pOtherMedicine')->nullable();
            $table->enum('pOthMedDrugGrouping', ['NCD', 'ANTIBIOTIC','OTHERS'])->nullable()->index();
            $table->text('pRoute')->nullable();
            $table->integer('pQuantity')->nullable()->default(0);
            $table->double('pActualUnitPrice')->nullable()->default(0.00);
            $table->double('pTotalAmtPrice')->nullable()->default(0.00);
            $table->string('pInstructionQuantity', 50)->nullable();
            $table->string('pInstructionStrength', 50)->nullable();
            $table->string('pIsDispensed')->nullable();
            $table->date('pDateDispensed')->nullable();
            $table->string('pDispensingPersonnel', 200)->nullable();
            $table->enum('pIsApplicable', ['Y', 'N'])->default('N')->nullable();
            $table->date('pDateAdded')->nullable();
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_diagnosticexamresult', function (Blueprint $table) {
            $table->id();
            
            $table->string('pHciCaseNo', 21);
            $table->foreign('pHciCaseNo')->references('pHciCaseNo')->on('dd_enlistment')->restrictOnDelete();
            
            $table->string('pHciTransNo', 21);
            $table->foreign('pHciTransNo')->references('pHciTransNo')->on('dd_soap_consultation')->restrictOnDelete();
            
            $table->string('pPatientPin', 12);
            $table->enum('pPatientType',['MM', 'DD']);
            $table->string('pMemPin', 12);
            $table->string('pEffYear', 4);
            $table->timestamps();
        });

        Schema::create('dd_cbc', function (Blueprint $table) {
            $table->id();
            $table->text('pReferralFacility');
            $table->date('pLabDate');
            $table->string('pHematocrit', 50);
            $table->string('pHemoglobinG', 50);
            $table->string('pHemoglobinMmol', 50);
            $table->string('pMhcPg', 50);
            $table->string('pMhcFmol', 50);
            $table->string('pMchGhb', 50);
            $table->string('pMchcMmol', 50);
            $table->string('pMcvUm', 50);
            $table->string('pMcvFl', 50);
            $table->string('pWbc1000', 50);
            $table->string('pWbc10', 50);
            $table->string('pMyelocyte', 50);
            $table->string('pNeutrophilsBnd', 50);
            $table->string('pNeutrophilsSeg', 50);
            $table->string('pLympocytes', 50);
            $table->string('pMonocytes', 50);
            $table->string('pEosinophilis', 50);
            $table->string('pBasophilis', 50);
            $table->string('pPlatelet', 50);
            $table->date('pDateAdded');
            $table->enum('pStatus', ['D', 'N', 'X', 'W']);
            $table->integer('pDiagnosticLabFee');
            $table->enum('pReportStatus', ['V', 'U', 'F'])->nullable()->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_urinalysis', function (Blueprint $table) {
            $table->id();
            $table->text('pReferralFacility');
            $table->date('pLabDate');
            $table->string('pGravity', 50);
            $table->string('pAppearance', 50);
            $table->string('pColor', 50);
            $table->string('pGlucose', 50);
            $table->string('pProteins', 50);
            $table->string('pKetones', 50);
            $table->string('pPh', 50);
            $table->string('pRbCells', 50);
            $table->string('pWbCells', 50);
            $table->string('pBacteria', 50);
            $table->string('pCrystals', 50);
            $table->string('pBladderCell', 50);
            $table->string('pSquamousCell', 50);
            $table->string('pTubularCell', 50);
            $table->string('pBroadCasts', 50);
            $table->string('pEpithelialCast', 50);
            $table->string('pGranularCast', 50);
            $table->string('pHyalineCast', 50);
            $table->string('pRbcCast', 50);
            $table->string('pWaxyCast', 50);
            $table->string('pWcCast', 50);
            $table->string('pAlbumin', 50);
            $table->string('pPusCells', 50);
            $table->date('pDateAdded');
            $table->enum('pStatus', ['D', 'N', 'X', 'W']);
            $table->integer('pDiagnosticLabFee');
            $table->enum('pReportStatus', ['V', 'U', 'F'])->nullable()->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_chestxray', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
            
            $table->id();
            $table->text('pReferralFacility')->nullable();
            $table->date('pLabDate')->nullable();
            
            $table->string('pFindings', 11)->nullable();
            // $table->foreign('pFindings')->references('findings_id')->on('lib_chestxray_findings');
            
            $table->text('pRemarksFindings')->nullable();

            $table->string('pObservation', 11)->nullable();
            // $table->foreign('pObservation')->references('observation_id')->on('lib_chestxray_observation');

            $table->text('pRemarksObservation')->nullable();
            $table->date('pDateAdded')->nullable();
            $table->enum('pStatus', ['D', 'N', 'X', 'W']);
            $table->integer('pDiagnosticLabFee');
            $table->enum('pReportStatus', ['V', 'U', 'F'])->nullable()->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_sputum', function (Blueprint $table) {
            $table->id();
            $table->text('pReferralFacility')->nullable();
            $table->date('pLabDate')->nullable();
            $table->enum('pDataCollection', ['1', '2', '3', 'X'])->default('X')->nullable();
            $table->enum('pFindings', ['1', '2'])->nullable();
            $table->text('pRemarks')->nullable();
            $table->string('pNoPlusses', 5)->nullable();
            $table->date('pDateAdded')->nullable();
            $table->enum('pStatus', ['D', 'N', 'X', 'W']);
            $table->integer('pDiagnosticLabFee');
            $table->enum('pReportStatus', ['V', 'U', 'F'])->nullable()->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_icd');
        Schema::dropIfExists('dd_diagnostic');
        Schema::dropIfExists('dd_management');
        Schema::dropIfExists('dd_advice');
        Schema::dropIfExists('dd_medicine');
        Schema::dropIfExists('dd_diagnosticexamresult');
        Schema::dropIfExists('dd_cbc');
        Schema::dropIfExists('dd_urinalysis');
        Schema::dropIfExists('dd_chestxray');
        Schema::dropIfExists('dd_sputum');
    }
};
