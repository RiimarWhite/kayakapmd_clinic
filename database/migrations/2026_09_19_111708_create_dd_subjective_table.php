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
        Schema::create('dd_subjective', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('pIllnessHistory')->comment('History of Patient Illnesses');
            $table->text('pSignsSymptoms')->comment('Pertinent Signs and Symptoms on Admission ID');
            $table->text('pOtherComplaint')->nullable()->comment('Other Complaint
                            Note: Required if X is included in
                            pSignsSymptoms');
            $table->text('pPainSite')->nullable()->comment('Site of Pain if Pain Element in
                        Pertinent Signs and Symptoms on
                        Admission is checked
                        Note: Required if 38 is included in
                        pSignsSymptoms');
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
        Schema::dropIfExists('dd_subjective');
    }
};
