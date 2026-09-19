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
        Schema::create('dd_soap_consultation', function (Blueprint $table) {
            $table->string('pHciTransNo', 21)->primary()->comment('
                    S+ACCRE_NO+YYY
                    Y+MM+5 digits
                    series number =
                    S+XXXXXXXXX+YY
                    YY+MM+99999
                ');
            $table->string('pHciCaseNo', 21)->index('dd_soap_consultation_phcicaseno_foreign')->comment('FK -> dd_enlistment.pHciCaseNo');
            $table->date('pSoapDate')->comment('YYYY-MM-DD');
            $table->string('pPatientPin', 12);
            $table->enum('pPatientType', ['MM', 'DD'])->comment('MM - member, DD - dependent');
            $table->string('pMemPin', 12);
            $table->string('pEffYear', 4)->comment('Effectivity Year');
            $table->string('pATC', 10)->comment('Authorization Transaction Code
                                                Note: Use ‘WALKEDIN’ as value if
                                                pWalkedIn is ‘Y’');
            $table->enum('pIsWalkedIn', ['Y', 'N'])->comment('Is Patient Walked In');
            $table->string('pCoPay', 15)->comment('Patient Co-Payment Amount ');
            $table->date('pTransDate')->comment('YYYY-MM-DD');
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U')->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_soap_consultation');
    }
};
