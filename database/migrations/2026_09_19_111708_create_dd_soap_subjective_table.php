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
        Schema::create('dd_soap_subjective', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('px_consultcode_cn', 50)->nullable();
            $table->string('s_TransNo', 21)->nullable();
            $table->string('en_caseno', 21)->nullable();
            $table->string('chifcomplaint', 2000)->nullable();
            $table->text('dIllnessHistory')->comment('History of Patient Illnesses');
            $table->text('dSignsSymptoms')->comment('Pertinent Signs and Symptoms on Admission ID');
            $table->text('dOtherComplaint')->nullable()->comment('Other Complaint
                            Note: Required if X is included in
                            pSignsSymptoms');
            $table->text('dPainSite')->nullable()->comment('Site of Pain if Pain Element in
                        Pertinent Signs and Symptoms on
                        Admission is checked
                        Note: Required if 38 is included in
                        pSignsSymptoms');
            $table->enum('dReportStatus', ['V', 'U', 'F'])->nullable()->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('dDeficiencyRemarks')->nullable();
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
        Schema::dropIfExists('dd_soap_subjective');
    }
};
