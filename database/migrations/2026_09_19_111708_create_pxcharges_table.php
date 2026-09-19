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
        Schema::create('pxcharges', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->enum('transactiontype', ['CHARGES', 'VOID', 'DISCOUNT', 'PAYMENTS', 'COLLECTIONS'])->nullable();
            $table->string('docrefno', 100)->nullable();
            $table->dateTime('transdate')->nullable();
            $table->string('docname', 100)->nullable();
            $table->string('phic_lib_id', 100)->nullable();
            $table->string('servicerefno', 100)->nullable();
            $table->string('servicename', 100)->nullable();
            $table->tinyInteger('vatable')->nullable();
            $table->double('retail')->nullable();
            $table->double('quantity')->nullable();
            $table->float('total')->nullable();
            $table->float('discount')->nullable();
            $table->float('net_total')->nullable();
            $table->string('paymentrefno', 100)->nullable();
            $table->enum('payment_type', ['HMO', 'PHIC', 'POCKET'])->nullable();
            $table->string('consultationrefno', 100)->nullable();
            $table->string('pxcode_pin', 50)->nullable();
            $table->string('pxname', 120)->nullable();
            $table->string('group_category_id', 5)->nullable()->comment('DRUGS / LAB / XRAY / CTSCAN / CSR / OTHERS');
            $table->string('group_category', 100)->nullable()->comment('DRUGS and MEDS / LABORATORY  / XRAY / CTSCAN / CENTRAL SUPPLIES / OTHERS');
            $table->string('paymentmethod', 100)->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('updated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pxcharges');
    }
};
