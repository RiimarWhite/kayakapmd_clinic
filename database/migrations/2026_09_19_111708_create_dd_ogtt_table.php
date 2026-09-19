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
        Schema::create('dd_ogtt', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('pReferralFacility')->nullable()->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for OGTT ');
            $table->string('pExamFastingMg', 50)->nullable()->comment('Result in Fasting Examination (mg/dL)');
            $table->string('pExamFastingMmol', 50)->nullable()->comment('Result in Fasting Examination (mmol/L)');
            $table->string('pExamOgttOneHrMg', 50)->nullable()->comment('Result in OGTT 1 Hour Examination (mg/dL)');
            $table->string('pExamOgttOneHrMmol', 50)->nullable()->comment('Result in OGTT 1 Hour Examination (mmol/L)');
            $table->string('pExamOgttTwoHrMg', 50)->nullable()->comment('Result in OGTT 2 Hours Examination (mg/dL)');
            $table->string('pExamOgttTwoHrMmol', 50)->nullable()->comment('Result in OGTT 2 Hours Examination (mmol/L)');
            $table->date('pDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])->nullable()->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->decimal('pDiagnosticLabFee', 10)->nullable();
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
        Schema::dropIfExists('dd_ogtt');
    }
};
