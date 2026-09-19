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
        Schema::create('dd_urinalysis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('pReferralFacility');
            $table->date('pLabDate');
            $table->string('pGravity', 50);
            $table->string('pAppearance', 50);
            $table->string('pColor', 50);
            $table->string('pGlucose', 50);
            $table->string('pProteins', 50);
            $table->string('pKetones', 50);
            $table->string('pPh', 50);
            $table->string('pRbCells', 50);
            $table->string('pWbCells', 50);
            $table->string('pBacteria', 50);
            $table->string('pCrystals', 50);
            $table->string('pBladderCell', 50);
            $table->string('pSquamousCell', 50);
            $table->string('pTubularCell', 50);
            $table->string('pBroadCasts', 50);
            $table->string('pEpithelialCast', 50);
            $table->string('pGranularCast', 50);
            $table->string('pHyalineCast', 50);
            $table->string('pRbcCast', 50);
            $table->string('pWaxyCast', 50);
            $table->string('pWcCast', 50);
            $table->string('pAlbumin', 50);
            $table->string('pPusCells', 50);
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
        Schema::dropIfExists('dd_urinalysis');
    }
};
