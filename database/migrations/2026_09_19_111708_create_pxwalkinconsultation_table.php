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
        Schema::create('pxwalkinconsultation', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->enum('source_data', ['ONLINE', 'QUELINE', 'SECRETARY', 'DOCTOR', 'ADMIN'])->nullable();
            $table->string('consultationrefno', 50)->nullable();
            $table->string('pxrefno', 50)->nullable();
            $table->string('pincode', 21)->nullable();
            $table->string('caseno', 21)->nullable();
            $table->string('en_transno', 21)->nullable();
            $table->string('en_caseno', 21)->nullable();
            $table->string('soap_caseno', 21)->nullable();
            $table->string('patientname', 180)->nullable();
            $table->string('pxlastname', 80)->nullable();
            $table->string('pxfirstname', 80)->nullable();
            $table->string('pxmidname', 80)->nullable();
            $table->string('pxsuffix', 10)->nullable();
            $table->string('phic_pin', 30)->nullable();
            $table->longText('secretary_note')->nullable();
            $table->string('infectious_risk_type', 80)->nullable();
            $table->string('docrefno', 50)->nullable();
            $table->string('docname', 120)->nullable();
            $table->enum('doctors_infectious_risk', ['NONE', 'LOW', 'MID', 'HIGH', 'SEVERLY HIGH'])->nullable();
            $table->string('gender', 255)->nullable();
            $table->date('birthday')->nullable();
            $table->tinyInteger('age')->nullable();
            $table->string('doccoaopd', 50)->nullable();
            $table->string('mobilenumber', 20)->nullable();
            $table->string('emailaddress', 60)->nullable();
            $table->string('classification', 80)->nullable();
            $table->string('subclassification', 80)->nullable();
            $table->double('weight')->nullable();
            $table->string('wunit', 10)->nullable();
            $table->double('height')->nullable();
            $table->string('hunit', 10)->nullable();
            $table->double('temp')->nullable();
            $table->string('tempunit', 10)->nullable();
            $table->dateTime('requesteddate')->nullable();
            $table->string('requestedby', 100)->nullable();
            $table->tinyInteger('consulted')->nullable();
            $table->dateTime('consulteddate')->nullable();
            $table->string('paymentrefno', 50)->nullable();
            $table->longText('reasonforconsultation')->nullable();
            $table->longText('impression')->nullable();
            $table->longText('finadiagnosis');
            $table->longText('pe_evaluation')->nullable();
            $table->tinyInteger('followup')->nullable();
            $table->dateTime('verifiedpaymentdate')->nullable();
            $table->string('verifiedby', 50)->nullable();
            $table->dateTime('followupdate')->nullable();
            $table->float('respiratoryrate', 20)->nullable();
            $table->float('pulserate', 20)->nullable();
            $table->float('bpnumerator', 20)->nullable();
            $table->float('bpdenominator', 20)->nullable();
            $table->boolean('followupcheckup')->nullable();
            $table->string('modeofpayment', 50)->nullable();
            $table->string('doctorsgroup', 50)->nullable();
            $table->tinyInteger('transactionstat')->nullable();
            $table->string('transactionstatby', 50)->nullable();
            $table->dateTime('transactionstatdate')->nullable();
            $table->boolean('forward_payment_status')->nullable();
            $table->string('deny_reason', 500)->nullable();
            $table->string('secrefno', 60)->nullable();
            $table->string('recordedby', 255)->nullable();
            $table->dateTime('recordeddate')->nullable();
            $table->string('instructions', 100)->nullable();
            $table->string('radiologypath', 255)->nullable();
            $table->string('laboratorypath', 255)->nullable();
            $table->enum('status', ['PENDING', 'FOR CONFIRMATION', 'WAITING', 'IN_CONSULTATION', 'COMPLETED', 'CANCELLED', 'NO_SHOW', 'UNSCHEDULED'])->nullable();
            $table->dateTime('consultation_date')->nullable();
            $table->string('queueno', 100)->nullable();
            $table->string('lastpayreferenceno', 35)->nullable();
            $table->double('gravida')->nullable();
            $table->double('para')->nullable();
            $table->double('abortion')->nullable();
            $table->double('iufd')->nullable();
            $table->double('died')->nullable();
            $table->tinyInteger('pathologic')->nullable();
            $table->string('linkaccount', 20)->nullable();
            $table->tinyInteger('infacilitydelivery')->nullable();
            $table->string('hmocode', 80)->nullable();
            $table->string('hmoname', 80)->nullable();
            $table->string('slcode', 30)->nullable();
            $table->tinyInteger('inbound_referral')->nullable();
            $table->string('inbound_referredby_md', 80)->nullable();
            $table->tinyInteger('outbound_referral')->nullable();
            $table->string('outbound_referredby_md', 80)->nullable();
            $table->tinyInteger('foradmit')->nullable();
            $table->longText('foradmit_instructions')->nullable();
            $table->tinyInteger('foryakap')->nullable();
            $table->string('yakap_validatedby', 80)->nullable();
            $table->dateTime('yakap_validated')->nullable();
            $table->string('forcheckup_confirmedby', 80)->nullable();
            $table->dateTime('forcheckup_confirmed')->nullable();
            $table->string('photo_path', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pxwalkinconsultation');
    }
};
