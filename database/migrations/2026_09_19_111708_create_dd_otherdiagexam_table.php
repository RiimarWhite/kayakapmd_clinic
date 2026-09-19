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
        Schema::create('dd_otherdiagexam', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('pReferralFacility')->nullable();
            $table->date('pLabDate')->nullable();
            $table->text('pOthDiagExam')->nullable();
            $table->text('pFindings')->nullable();
            $table->date('pDateAdded')->nullable();
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])->nullable();
            $table->integer('pDiagnosticLabFee')->nullable();
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
        Schema::dropIfExists('dd_otherdiagexam');
    }
};
