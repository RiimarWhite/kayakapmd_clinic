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
        Schema::create('dd_medicine', function (Blueprint $table) {
            $table->string('pHciCaseNo', 21)->index('dd_medicine_phcicaseno_foreign');
            $table->string('pHciTransNo', 21)->index('dd_medicine_phcitransno_foreign');
            $table->string('pCategory')->nullable()->index();
            $table->string('pDrugCode')->nullable()->index();
            $table->string('pGenericCode')->nullable()->index();
            $table->string('pSaltCode')->nullable()->index();
            $table->string('pStrengthCode')->nullable()->index();
            $table->string('pFormCode')->nullable()->index();
            $table->string('pUnitCode')->nullable()->index();
            $table->string('pPackageCode')->nullable()->index();
            $table->text('pOtherMedicine')->nullable();
            $table->enum('pOthMedDrugGrouping', ['NCD', 'ANTIBIOTIC', 'OTHERS'])->nullable()->index();
            $table->text('pRoute')->nullable();
            $table->integer('pQuantity')->nullable()->default(0);
            $table->double('pActualUnitPrice')->nullable()->default(0);
            $table->double('pTotalAmtPrice')->nullable()->default(0);
            $table->string('pInstructionQuantity', 50)->nullable();
            $table->string('pInstructionStrength', 50)->nullable();
            $table->string('pIsDispensed')->nullable();
            $table->date('pDateDispensed')->nullable();
            $table->string('pDispensingPersonnel', 200)->nullable();
            $table->enum('pIsApplicable', ['Y', 'N'])->nullable()->default('N');
            $table->date('pDateAdded')->nullable();
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
        Schema::dropIfExists('dd_medicine');
    }
};
