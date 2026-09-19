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
        Schema::create('dd_diag_otherdiagexam', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('px_consultcode_cn', 50)->nullable();
            $table->string('en_CaseNo', 21)->nullable();
            $table->string('s_TransNo', 21)->nullable();
            $table->string('dReferralFacility', 1000)->nullable();
            $table->date('dLabDate')->nullable();
            $table->string('dOthDiagExam', 1000)->nullable();
            $table->string('dFindings', 1000)->nullable();
            $table->date('dDateAdded')->nullable();
            $table->enum('dStatus', ['D', 'N', 'X', 'W', 'V'])->nullable();
            $table->double('dDiagnosticLabFee')->nullable();
            $table->enum('dReportStatus', ['V', 'U', 'F'])->nullable();
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
        Schema::dropIfExists('dd_diag_otherdiagexam');
    }
};
