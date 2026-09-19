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
        Schema::create('pxsettlements', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('transactionrefno', 50)->nullable();
            $table->string('consultationrefno', 50)->nullable();
            $table->string('pincode', 50)->nullable();
            $table->string('docrefno', 50)->nullable();
            $table->string('docname', 120)->nullable();
            $table->double('total_doctorspf')->nullable();
            $table->double('total_vaccines')->nullable();
            $table->double('total_immunizations')->nullable();
            $table->double('total_meds')->nullable();
            $table->double('total_lab')->nullable();
            $table->double('total_xray')->nullable();
            $table->double('total_procedures')->nullable();
            $table->double('total_supplies')->nullable();
            $table->double('total_others')->nullable();
            $table->double('total_gross')->nullable();
            $table->double('less_vat')->nullable();
            $table->double('less_srpwd')->nullable();
            $table->double('less_hmo')->nullable();
            $table->double('less_phic')->nullable();
            $table->double('less_govt')->nullable();
            $table->double('less_discount')->nullable();
            $table->double('net_payable')->nullable();
            $table->double('payment_cash')->nullable();
            $table->double('payment_card')->nullable();
            $table->double('payment_wallet')->nullable();
            $table->double('payment_pn')->nullable();
            $table->string('hmocode', 50)->nullable();
            $table->string('hmoname', 180)->nullable();
            $table->string('hmo_type', 80)->nullable();
            $table->string('createdby', 80)->nullable();
            $table->dateTime('created')->nullable();
            $table->string('cashierbatch', 50)->nullable();
            $table->date('cashier_date')->nullable();
            $table->string('cashiername', 80)->nullable();
            $table->string('journalcode', 80)->nullable();
            $table->string('slcode', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pxsettlements');
    }
};
