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
        // Schema::create('doctor_schedules', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('doctors_id')->constrained()->onDelete('cascade');
        //     $table->date('schedule_date');
        //     $table->time('start_time');
        //     $table->time('end_time');
        //     $table->timestamps();
        // });

        Schema::create('dd_enlistment', function (Blueprint $table) {
            $table->string('pHciCaseNo', 21)->unique();
            $table->string('pHciTransNo', 21)->primary();
            $table->string('pEffYear', 4);
            $table->enum('pEnlistStat', [1, 2, 3]);
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
            $table->enum('pPatientSex', ['F','M']);
            $table->date('pPatientDob');
            $table->enum('pPatientType', ['MM','DD']);
            $table->string('pPatientMobileNo', 15);
            $table->string('pPatientLandlineNo',15)->nullable();
            $table->enum('pWithConsent', ['Y', 'N', 'X']);
            $table->date('pTransDate');
            $table->string('pCreatedBy', 30);
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_profile', function (Blueprint $table) {
            $table->string('pHciTransNo', 21)->primary();
            $table->string('pHciCaseNo', 21);
            $table->foreign('pHciCaseNo')->references('pHciCaseNo')->on('dd_enlistment')->restrictOnDelete();
            $table->date('pProfDate');
            $table->string('pPatientPin', 12);
            $table->enum('pPatientType', ['MM', 'DD']);
            $table->string('pPatientAge');
            $table->string('pMemPin', 12);
            $table->string('pEffyear', 4);
            $table->string('pATC', 10);
            $table->enum('pIsWalkedIn', ['Y', 'N']);
            $table->date('pTransDate');
            $table->enum('pReportStatus', ['V', 'U', 'F']);
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_profile');
        Schema::dropIfExists('dd_enlistment');
    }
};
