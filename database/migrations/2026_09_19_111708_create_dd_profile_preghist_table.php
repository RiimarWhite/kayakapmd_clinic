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
        Schema::create('dd_profile_preghist', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('p_TransNo', 21)->nullable();
            $table->string('en_caseno', 21)->nullable();
            $table->float('dPregCnt')->unsigned()->nullable();
            $table->float('dDeliveryCnt')->unsigned()->nullable();
            $table->enum('dDeliveryTyp', ['N', 'O', 'B', 'X', ''])->nullable()->comment('N = Normal (NSD), O = Operative (CSD), B = Both (NSD & CSD), X = Not Applicable');
            $table->float('dFullTermCnt')->unsigned()->nullable();
            $table->float('dPrematureCnt')->unsigned()->nullable();
            $table->float('dAbortionCnt')->unsigned()->nullable();
            $table->float('dLivChildrenCnt')->unsigned()->nullable();
            $table->enum('dWPregIndhyp', ['Y', 'N', ''])->nullable();
            $table->enum('dWFamPlan', ['Y', 'N', ''])->nullable();
            $table->enum('dIsApplicable', ['Y', 'N', ''])->nullable();
            $table->enum('dReportStatus', ['V', 'U', 'F', ''])->nullable()->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->string('dDeficiencyRemarks', 2000)->nullable();
            $table->string('createdby', 80)->nullable();
            $table->dateTime('created')->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('updated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_profile_preghist');
    }
};
