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
        Schema::create('dd_profile', function (Blueprint $table) {
            $table->string('pHciTransNo', 21)->primary();
            $table->string('pHciCaseNo', 21)->index('dd_profile_phcicaseno_foreign');
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
    }
};
