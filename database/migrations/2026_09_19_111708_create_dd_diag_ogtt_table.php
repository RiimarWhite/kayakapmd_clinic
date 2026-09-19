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
        Schema::create('dd_diag_ogtt', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('px_consultcode_cn', 50)->nullable();
            $table->string('en_CaseNo', 21)->nullable();
            $table->string('s_TransNo', 21)->nullable();
            $table->string('dReferralFacility', 1000)->nullable()->comment('Name of Referral Facility, if referred');
            $table->date('dLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for OGTT ');
            $table->string('dExamFastingMg', 50)->nullable()->comment('Result in Fasting Examination (mg/dL)');
            $table->string('dExamFastingMmol', 50)->nullable()->comment('Result in Fasting Examination (mmol/L)');
            $table->string('dExamOgttOneHrMg', 50)->nullable()->comment('Result in OGTT 1 Hour Examination (mg/dL)');
            $table->string('dExamOgttOneHrMmol', 50)->nullable()->comment('Result in OGTT 1 Hour Examination (mmol/L)');
            $table->string('dExamOgttTwoHrMg', 50)->nullable()->comment('Result in OGTT 2 Hours Examination (mg/dL)');
            $table->string('dExamOgttTwoHrMmol', 50)->nullable()->comment('Result in OGTT 2 Hours Examination (mmol/L)');
            $table->date('dDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('dStatus', ['D', 'N', 'X', 'W', 'V'])->nullable()->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->double('dDiagnosticLabFee')->nullable();
            $table->enum('dReportStatus', ['V', 'U', 'F'])->nullable()->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->string('dDeficiencyRemarks', 2000)->nullable();
            $table->string('createdby', 80)->nullable();
            $table->dateTime('created')->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('updated')->nullable();
            $table->string('charge_transcode', 50)->nullable()->comment('transcode of stock_ledger');
            $table->string('prodcode', 50)->nullable()->comment('prodcode of item in the stock_lisitng');
            $table->double('clinic_cost')->nullable()->comment('cost from stocks_listing');
            $table->string('pricetype', 10)->nullable()->comment('HMO, regular, PHIC, etc, base on stocks_listing');
            $table->double('co_pay')->nullable();
            $table->double('vat_amt')->nullable();
            $table->enum('entry_type', ['PHIC', 'INTERNAL'])->nullable();
            $table->string('resultcode', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_diag_ogtt');
    }
};
