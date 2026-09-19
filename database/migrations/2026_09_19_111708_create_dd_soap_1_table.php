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
        Schema::create('dd_soap_1', function (Blueprint $table) {
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('pHciTransNo', 21)->nullable()->comment('
                    S+ACCRE_NO+YYY
                    Y+MM+5 digits
                    series number =
                    S+XXXXXXXXX+YY
                    YY+MM+99999
                ');
            $table->string('px_pin', 50)->nullable();
            $table->string('px_consultcode_cn', 50)->nullable();
            $table->string('en_CaseNo', 21)->nullable()->comment('FK -> dd_enlistment.pHciCaseNo');
            $table->date('dSoapDate')->comment('YYYY-MM-DD');
            $table->string('dPatientPin', 12)->nullable();
            $table->enum('dPatientType', ['MM', 'DD'])->nullable()->comment('MM - member, DD - dependent');
            $table->string('dMemPin', 12)->nullable();
            $table->string('dEffYear', 4)->nullable()->comment('Effectivity Year');
            $table->string('dATC', 10)->nullable()->comment('Authorization Transaction Code
                                                Note: Use ‘WALKEDIN’ as value if
                                                pWalkedIn is ‘Y’');
            $table->enum('dIsWalkedIn', ['Y', 'N'])->nullable()->comment('Is Patient Walked In');
            $table->double('dCoPay')->nullable()->comment('Patient Co-Payment Amount ');
            $table->date('dTransDate')->comment('YYYY-MM-DD');
            $table->enum('dReportStatus', ['V', 'U', 'F'])->nullable()->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('dDeficiencyRemarks')->nullable();
            $table->string('createdby', 80)->nullable();
            $table->dateTime('created')->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('updated')->nullable();
            $table->string('services_made', 180)->nullable()->comment('dd_soap_services_type');
            $table->double('total_payable')->nullable();
            $table->double('cta_others')->nullable();
            $table->double('cta_phic')->nullable();
            $table->double('co_pay')->nullable();
            $table->enum('status', ['PENDING', 'CONSULTED', 'CANCELLED'])->nullable();
            $table->string('report_code', 80)->nullable()->comment('tranche first report code');
            $table->date('report_date')->nullable()->comment('tranche first report date');
            $table->string('report_code2', 80)->nullable()->comment('tranche Second report code');
            $table->date('report_date2')->nullable()->comment('tranche second report date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_soap_1');
    }
};
