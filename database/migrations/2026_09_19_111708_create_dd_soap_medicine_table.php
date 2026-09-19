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
        Schema::create('dd_soap_medicine', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('px_consultcode_cn', 50)->nullable();
            $table->string('en_CaseNo', 21)->nullable()->comment('enlistment table T');
            $table->string('s_TransNo', 21)->nullable()->comment('SOAP table - S');
            $table->string('prodcode', 50)->nullable();
            $table->string('prod_itemdscr', 220)->nullable();
            $table->string('dCategory', 50)->nullable();
            $table->string('dDrugCode', 30)->nullable();
            $table->string('dGenericCode', 5)->nullable();
            $table->string('dSaltCode', 5)->nullable();
            $table->string('dStrengthCode', 5)->nullable();
            $table->string('dFormCode', 5)->nullable();
            $table->string('dUnitCode', 5)->nullable();
            $table->string('dPackageCode', 5)->nullable();
            $table->string('dOtherMedicine', 500)->nullable()->comment('500');
            $table->enum('dOthMedDrugGrouping', ['NCD', 'ANTIBIOTIC', 'OTHERS'])->nullable();
            $table->string('dRoute', 500)->nullable();
            $table->double('dQuantity')->nullable();
            $table->double('dActualUnitPrice')->nullable();
            $table->double('dTotalAmtPrice')->nullable();
            $table->string('dInstructionQuantity', 50)->nullable();
            $table->string('dInstructionStrength', 50)->nullable();
            $table->string('dInstructionFrequency', 50)->nullable();
            $table->string('dInstructionPhysician', 200)->nullable();
            $table->string('dIsDispensed', 1)->nullable();
            $table->date('dDateDispensed')->nullable();
            $table->string('dDispensingPersonnel', 200)->nullable();
            $table->enum('dIsApplicable', ['Y', 'N'])->nullable();
            $table->date('dDateAdded')->nullable();
            $table->enum('dReportStatus', ['V', 'U', 'F'])->nullable();
            $table->text('dDeficiencyRemarks')->nullable()->comment('2000');
            $table->string('createdby', 80)->nullable();
            $table->dateTime('created')->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('updated')->nullable();
            $table->double('retail_price')->nullable();
            $table->double('qty')->nullable();
            $table->double('total_amt')->nullable();
            $table->enum('chargetype', ['CTP', 'PHIC', 'HMO', 'CTA'])->nullable()->comment('CTP = CHARGE TO PATIENT, CTA = CHARGE TO ACCOUNT');
            $table->string('charge_refcode', 50)->nullable();
            $table->string('servicerefno', 50)->nullable();
            $table->double('clinic_cost')->nullable();
            $table->double('clinic_srp')->nullable();
            $table->string('pricetype', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_soap_medicine');
    }
};
