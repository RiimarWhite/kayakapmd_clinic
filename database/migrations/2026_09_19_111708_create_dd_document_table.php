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
        Schema::create('dd_document', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 50)->nullable();
            $table->string('px_consultcode_cn', 50)->nullable();
            $table->string('pHciCaseNo', 21)->nullable()->comment('FK -> dd_enlistment.pHciCaseNo');
            $table->string('pHciTransNo', 21)->nullable()->comment('FK -> dd_soap_consultation.pHciTransNo');
            $table->string('pPatientPin', 12)->nullable()->comment('Refer to Members PIN if patient type is MM; Refer to Dependents PIN if type is DD ');
            $table->enum('pPatientType', ['MM', 'DD'])->nullable()->comment('MM - Member, DD - Dependent');
            $table->string('pMemPin', 12)->nullable()->comment('Philhealth Identification Number (PIN) of Primary Member');
            $table->enum('pDocumentType', ['EKAS', 'EPRESS', 'OTH'])->nullable()->comment('“EKAS” - electronic KonSulTa
                        Slip
                        “EPRESS” - electronic
                        Prescription Slip
                        “OTH” - Others');
            $table->text('pDocumentUrl')->nullable()->comment('URL of document attachment for download');
            $table->date('pTransDate')->nullable()->comment('YYYY-MM-DD | Date when the record inserted');
            $table->enum('pReportStatus', ['V', 'U', 'F'])->nullable()->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();
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
        Schema::dropIfExists('dd_document');
    }
};
