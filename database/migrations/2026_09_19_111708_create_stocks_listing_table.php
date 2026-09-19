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
        Schema::create('stocks_listing', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->enum('item_grouping', ['DRUGS AND MEDS', 'SUPPLIES', 'PROCEDURES', 'DIAGNOSTIC', 'IMAGING', 'PROFESSIONAL FEE'])->nullable();
            $table->string('prodcode', 20)->nullable()->comment('unquecode / YYYY#####    (year+seqno)');
            $table->string('phic_reference_code', 80)->nullable()->comment('reference on drugcode and lab/cray code');
            $table->string('drug_generic', 80)->nullable();
            $table->string('drug_brand', 80)->nullable();
            $table->string('drug_dosage', 80)->nullable()->comment('12g/10ml');
            $table->string('drug_preperation', 80)->nullable();
            $table->string('drug_add_dscr', 80)->nullable();
            $table->enum('drug_grouping', ['DRUGS AND MEDS', 'MEDICAL SUPPLIES'])->nullable();
            $table->string('drug_type', 30)->nullable()->comment('dw_lib_drugtype / NDC ANTIBIOTIC VACCINE OTHERS');
            $table->enum('drug_prescription_type', ['OTC', 'RX_REGULAR', 'RX_DANGEROUS'])->nullable()->comment('Over the Counter (OTC)');
            $table->string('unit', 20)->nullable()->comment('Tab, Ampule, Syrup, pc');
            $table->string('prod_itemdscr', 220)->nullable()->comment('Paracetamol - Biogesic 500mg Tab');
            $table->string('oecb_code', 50)->nullable()->comment('for ER use only');
            $table->double('oecb_price')->nullable()->comment('for ER use only');
            $table->tinyInteger('yakap_essential')->nullable();
            $table->string('yakap_essential_code', 50)->nullable()->comment('use if item is under yakap ESSENTIAL ');
            $table->double('yakap_essential_price')->nullable()->comment('use if item is under yakap ESSENTIAL ');
            $table->tinyInteger('pndf_enable')->nullable()->comment('PNDF');
            $table->tinyInteger('phic_enable')->nullable();
            $table->string('last_delivery_no', 50)->nullable();
            $table->date('last_delivery_date')->nullable();
            $table->double('last_delivery_cost')->nullable();
            $table->double('cost_ave')->nullable();
            $table->double('price_regular')->nullable();
            $table->double('price_phic')->nullable();
            $table->double('price_hmo')->nullable();
            $table->double('price_others')->nullable();
            $table->tinyInteger('is_inventory')->nullable();
            $table->double('qty')->nullable();
            $table->double('qty_level_reorder')->nullable();
            $table->string('po_code_lastdelivery', 50)->nullable();
            $table->string('supplierid', 50)->nullable();
            $table->string('suppliername', 120)->nullable();
            $table->string('remarks', 120)->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('updated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks_listing');
    }
};
