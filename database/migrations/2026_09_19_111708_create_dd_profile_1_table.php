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
        Schema::create('dd_profile_1', function (Blueprint $table) {
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('dTransNo', 21)->nullable()->comment('Prefix = P');
            $table->string('en_CaseNo', 21)->nullable()->comment('source elistment table');
            $table->string('px_pin', 50)->nullable();
            $table->date('dProfDate');
            $table->string('dPatientPin', 12)->nullable();
            $table->string('patientname', 200)->nullable();
            $table->enum('dPatientType', ['MM', 'DD'])->nullable();
            $table->string('dPatientAge')->nullable();
            $table->string('dMemPin', 12)->nullable();
            $table->string('dEffyear', 4)->nullable();
            $table->string('dATC', 10)->nullable();
            $table->enum('dIsWalkedIn', ['Y', 'N'])->nullable();
            $table->date('dTransDate');
            $table->enum('dReportStatus', ['V', 'U', 'F'])->nullable();
            $table->text('dDeficiencyRemakrs')->nullable();
            $table->dateTime('updated')->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('date_cancelled')->nullable()->comment('DATE WHEN USER CANCELLED THE PROFILE');
            $table->dateTime('date_transferred')->nullable()->comment('DATE WHEN USER TRANSFERRED THE PROFILE');
            $table->string('cancelledby', 20)->nullable()->comment('USER WHO CANCELLED THE PROFILE');
            $table->string('transferredby', 20)->nullable()->comment('USER WHO TRANSFERRED THE PROFILE TO OTHER INSTITUTION');
            $table->dateTime('date_for_payment')->nullable()->comment('DATE WHEN FLAGGED FOR PAYMENT');
            $table->string('for_paymentby', 20)->nullable()->comment('USER WHO FLAGGED FOR PAYMENT');
            $table->string('remarks', 100)->nullable()->comment('FURTHER REMARKS');
            $table->integer('presc_type')->nullable();
            $table->string('profile_otp', 10)->nullable()->comment('OTP FOR PATIENT RECORD');
            $table->string('report_trans_no', 25)->nullable()->comment('TRANSMITTAL NUMBER ON THE GENERATED REPORT');
            $table->string('is_finalize', 1)->nullable()->comment('Y - YES; N- NO');
            $table->string('xps_module', 50)->nullable()->comment('Module where the data came from');
            $table->string('with_atc', 1)->nullable()->comment('Y - with ATC; N - without ATC');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_profile_1');
    }
};
