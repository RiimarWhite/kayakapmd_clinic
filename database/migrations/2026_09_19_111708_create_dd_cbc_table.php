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
        Schema::create('dd_cbc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('pReferralFacility');
            $table->date('pLabDate');
            $table->string('pHematocrit', 50);
            $table->string('pHemoglobinG', 50);
            $table->string('pHemoglobinMmol', 50);
            $table->string('pMhcPg', 50);
            $table->string('pMhcFmol', 50);
            $table->string('pMchGhb', 50);
            $table->string('pMchcMmol', 50);
            $table->string('pMcvUm', 50);
            $table->string('pMcvFl', 50);
            $table->string('pWbc1000', 50);
            $table->string('pWbc10', 50);
            $table->string('pMyelocyte', 50);
            $table->string('pNeutrophilsBnd', 50);
            $table->string('pNeutrophilsSeg', 50);
            $table->string('pLympocytes', 50);
            $table->string('pMonocytes', 50);
            $table->string('pEosinophilis', 50);
            $table->string('pBasophilis', 50);
            $table->string('pPlatelet', 50);
            $table->date('pDateAdded');
            $table->enum('pStatus', ['D', 'N', 'X', 'W']);
            $table->integer('pDiagnosticLabFee');
            $table->enum('pReportStatus', ['V', 'U', 'F'])->nullable()->default('U');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_cbc');
    }
};
