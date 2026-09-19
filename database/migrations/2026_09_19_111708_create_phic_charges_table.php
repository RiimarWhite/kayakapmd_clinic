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
        Schema::create('phic_charges', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('px_consultcode_cn', 50)->nullable();
            $table->string('en_caseno', 27)->nullable();
            $table->string('s_TransNo', 27)->nullable();
            $table->enum('source_entry', ['SOAP', 'CLINIC'])->nullable();
            $table->string('pxname', 120)->nullable();
            $table->enum('item_grouping', ['DRUGS AND MEDS', 'SUPPLIES', 'PROCEDURES', 'DIAGNOSTIC', 'IMAGING', 'PROFESSIONAL FEE', 'OTHERS'])->nullable();
            $table->string('prod_code', 80)->nullable();
            $table->string('drug_code', 30)->nullable()->comment('CODE REF: GENCODE+SALTCODE+STRENGTHCODE+FORMCODE+UNITCODE+PACKAGECODE');
            $table->string('gen_code', 5)->nullable()->comment('GENERIC CODE OF MEDICINE');
            $table->string('salt_code', 5)->nullable();
            $table->string('strength_code', 5)->nullable()->comment('STRENGTH/DOSAGE CODE OF MEDICINE');
            $table->string('form_code', 5)->nullable()->comment('FORM/ROUTE/PREPARATION CODE OF MEDICINE');
            $table->string('unit_code', 5)->nullable();
            $table->string('package_code', 5)->nullable()->comment('PACKAGE CODE OF MEDICINE');
            $table->string('route', 1000)->nullable();
            $table->string('generic_name', 1000)->nullable();
            $table->double('prescribed_quantity')->nullable();
            $table->string('ins_strength', 50)->nullable()->comment('STOCK DOSAGE OF MEDICINE PER INSTRUCTION');
            $table->string('ins_frequency', 50)->nullable()->comment('FREQUENCY OF MEDICINE PER INSTRUCTION');
            $table->double('qty')->nullable()->comment('NUMBER OF MEDICINES PRESCRIBED');
            $table->string('unit', 50)->nullable();
            $table->tinyInteger('isvatable')->nullable();
            $table->double('actual_price')->nullable()->comment('DRUG ACTUAL PRICE LOOK - unit price');
            $table->double('phic_price')->nullable();
            $table->double('co_payment')->nullable()->comment('COPAYMENT OF PATIENT (total_price)');
            $table->double('total_vatamt')->nullable()->comment('(total_price / 1.12) x 0.12');
            $table->double('total_price')->nullable()->comment('qty * actual_PRICE');
            $table->string('doc_code', 50)->nullable();
            $table->string('doc_name', 200)->nullable()->comment('PRESCRIBING DOCTOR/PHYSICIAN');
            $table->string('is_applicable', 1)->nullable();
            $table->string('checkup_trans_no', 25)->nullable()->comment('TRANSMITTAL NUMBER ON THE GENERATED REPORT');
            $table->enum('status', ['U', 'V', 'F'])->nullable()->comment('U - UNVALIDATED; V - VALIDATED; F - FAILED');
            $table->string('deficiency_remarks', 500)->nullable()->comment('REMARKS OF THE REPORT STATUS');
            $table->dateTime('updated')->comment('DATE AND TIME THE RECORD WAS ADDED ---SYSDATE');
            $table->string('updatedby', 20)->nullable()->comment('REFER TO THE USER WHO ADDED THE RECORD ---LOGGED USER');
            $table->enum('is_dispensed', ['Y', 'N'])->nullable();
            $table->date('dispensed_date')->nullable();
            $table->string('dispensedby', 80)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('sub_grouping', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phic_charges');
    }
};
