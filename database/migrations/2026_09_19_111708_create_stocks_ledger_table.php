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
        Schema::create('stocks_ledger', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('dw_clientcode', 12)->nullable();
            $table->enum('transactiontype', ['CHARGES', 'RETURNS', 'VOID', 'PAYMENTS', 'DISCOUNT', 'COLLECTION'])->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('px_consultcode_cn', 50)->nullable();
            $table->string('en_CaseNo', 21)->nullable();
            $table->string('s_transno', 21)->nullable();
            $table->string('patient_name', 120)->nullable();
            $table->string('prodcode', 20)->nullable()->comment('unquecode / YYYY#####    (year+seqno)');
            $table->string('phic_reference_code', 80)->nullable()->comment('reference on drugcode and lab/cray code');
            $table->string('item_dscr', 220)->nullable()->comment('Paracetamol - Biogesic 500mg Tab');
            $table->string('price_type', 30)->nullable();
            $table->tinyInteger('yakap_essential')->nullable();
            $table->double('yakap_essential_price')->nullable()->comment('use if item is under yakap ESSENTIAL ');
            $table->string('hmocode', 50)->nullable();
            $table->string('hmoname', 120)->nullable();
            $table->tinyInteger('pndf_enable')->nullable()->comment('PNDF');
            $table->tinyInteger('phic_enable')->nullable();
            $table->double('cost_ave')->nullable();
            $table->double('retails')->nullable();
            $table->double('qty')->nullable();
            $table->string('unit', 20)->nullable()->comment('Tab, Ampule, Syrup, pc');
            $table->double('vatamt')->nullable();
            $table->double('totalamt')->nullable();
            $table->enum('item_grouping', ['DRUGS AND MEDS', 'SUPPLIES', 'PROCEDURES', 'DIAGNOSTIC', 'IMAGING', 'PROFESSIONAL FEE'])->nullable();
            $table->string('sub_grouping', 50)->nullable();
            $table->string('remarks', 120)->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('updated')->nullable();
            $table->string('dispenseby', 80)->nullable();
            $table->dateTime('dispensed')->nullable();
            $table->enum('dispensed_status', ['PENDING', 'RELEASED', 'CANCELLED'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks_ledger');
    }
};
