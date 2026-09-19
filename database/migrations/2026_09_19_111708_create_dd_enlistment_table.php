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
        Schema::create('dd_enlistment', function (Blueprint $table) {
            $table->string('pHciCaseNo', 21)->unique();
            $table->string('pHciTransNo', 21)->primary();
            $table->string('pEffYear', 4);
            $table->enum('pEnlistStat', ['1', '2', '3']);
            $table->date('pEnlistDate');
            $table->enum('pPackageType', ['P', 'E', 'K']);
            $table->string('pMemPin');
            $table->string('pMemFname', 30);
            $table->string('pMemMname', 30)->nullable();
            $table->string('pMemLname', 30);
            $table->string('pMemExtname', 30)->nullable();
            $table->date('pMemDob');
            $table->string('pPatientPin', 12);
            $table->string('pPatientFname', 12);
            $table->string('pPatientMname', 30);
            $table->string('pPatientLname', 30);
            $table->string('pPatientExtname', 30)->nullable();
            $table->enum('pPatientSex', ['F', 'M']);
            $table->date('pPatientDob');
            $table->enum('pPatientType', ['MM', 'DD']);
            $table->string('pPatientMobileNo', 15);
            $table->string('pPatientLandlineNo', 15)->nullable();
            $table->enum('pWithConsent', ['Y', 'N', 'X']);
            $table->date('pTransDate');
            $table->string('pCreatedBy', 30);
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_enlistment');
    }
};
