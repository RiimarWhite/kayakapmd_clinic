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
        Schema::create('dd_preghist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('pPregCnt')->nullable();
            $table->unsignedInteger('pDeliveryCnt')->nullable();
            $table->enum('pDeliveryTyp', ['N', 'O', 'B', 'X'])->nullable()->comment('N = Normal (NSD), O = Operative (CSD), B = Both (NSD & CSD), X = Not Applicable');
            $table->unsignedInteger('pFullTermCnt')->nullable();
            $table->unsignedInteger('pPrematureCnt')->nullable();
            $table->unsignedInteger('pAbortionCnt')->nullable();
            $table->unsignedInteger('pLivChildrenCnt')->nullable();
            $table->enum('pWPregIndhyp', ['Y', 'N'])->nullable();
            $table->enum('pWFamPlan', ['Y', 'N']);
            $table->enum('pIsApplicable', ['Y', 'N']);
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
        Schema::dropIfExists('dd_preghist');
    }
};
