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
        Schema::create('dd_enlistment_1', function (Blueprint $table) {
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('dCaseNo', 21)->nullable();
            $table->string('dTransNo', 21)->nullable();
            $table->string('dEffyear', 4)->nullable();
            $table->string('px_consultcode_cn', 100)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->enum('dEnlistStat', ['1', '2', '3'])->nullable()->comment('1Active 2cancelled 3transferred ');
            $table->date('dEnlistDate');
            $table->enum('dPackageType', ['P', 'E', 'K'])->nullable();
            $table->string('dMemPin')->nullable();
            $table->string('dMemFname', 30)->nullable();
            $table->string('dMemMname', 30)->nullable();
            $table->string('dMemLname', 30)->nullable();
            $table->string('dMemExtname', 30)->nullable();
            $table->date('dMemDob');
            $table->string('dPatientPin', 12)->nullable();
            $table->string('dPatientFname', 12)->nullable();
            $table->string('dPatientMname', 30)->nullable();
            $table->string('dPatientLname', 30)->nullable();
            $table->string('dPatientExtname', 30)->nullable();
            $table->string('patientname', 200)->nullable();
            $table->enum('dPatientSex', ['F', 'M'])->nullable();
            $table->date('dPatientDob');
            $table->enum('dPatientType', ['MM', 'DD'])->nullable();
            $table->string('dPatientMobileNo', 15)->nullable();
            $table->string('dPatientLandlineNo', 15)->nullable();
            $table->enum('dWithConsent', ['Y', 'N', 'X'])->nullable();
            $table->date('dTransDate');
            $table->dateTime('created')->nullable();
            $table->string('dCreatedBy', 30)->nullable();
            $table->enum('dReportStatus', ['V', 'U', 'F'])->nullable();
            $table->text('dDeficiencyRemarks')->nullable();
            $table->dateTime('updated')->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->string('for_payment', 1)->nullable()->comment('FLAG FOR PAYMENT (Y=YES; N=NO)');
            $table->string('with_LOA', 1)->nullable()->comment('FLAG FOR WITH LETTER OF AUTHORIZATION (Y=YES; N=NO)');
            $table->string('with_consent', 1)->nullable()->comment('FLAG FOR WITH CONSENT (Y=YES; N=NO)');
            $table->dateTime('date_cancelled')->nullable()->comment('DATE WHEN USER CANCELLED THE PROFILE');
            $table->string('cancelledby', 20)->nullable()->comment('USER WHO CANCELLED THE PROFILE');
            $table->dateTime('date_transferred')->nullable()->comment('DATE WHEN USER TRANSFERRED THE PROFILE');
            $table->string('transferredby', 20)->nullable()->comment('USER WHO TRANSFERRED THE PROFILE TO OTHER INSTITUTION');
            $table->string('transferred_emr_provider_id', 20)->nullable()->comment('ELECTRONIC MEDICAL RECORD PROVIDER ID');
            $table->string('transferred_facilitycode', 20)->nullable()->comment('DOH Facility Code of the Health Facility');
            $table->string('is_dependent_valid', 1)->nullable()->comment('1-IF VALID DEPENDENT; 0-INVALID DEPENDENT (1- TRUE; 0-FALSE)');
            $table->string('with_disability', 1)->nullable()->comment('1-WITH DISABILITY; 0-W/OUT DISABILITY(1- TRUE; 0-FALSE)');
            $table->string('dependent_type', 6)->nullable()->comment('SPOUSE, CHILD OR PARENT');
            $table->string('avail_free_service', 1)->nullable()->comment('Y - YES; N - NO');
            $table->string('XPS_MODULE', 50)->nullable();
            $table->string('report_trans_no', 25)->nullable()->comment('TRANSMITTAL NUMBER ON THE GENERATED REPORT');
            $table->string('cf4_claimid_no', 20)->nullable()->comment('CF4 - CLAIM ID NUMBER');
            $table->string('cf4_hci_transmittal_no', 20)->nullable()->comment('CF4 - HCI ECLAIMS TRANSMITAL ID NUMBER PER CLAIM');
            $table->string('px_mobileno', 15)->nullable();
            $table->string('px_landline', 15)->nullable();
            $table->string('px_emailadd', 80)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_enlistment_1');
    }
};
