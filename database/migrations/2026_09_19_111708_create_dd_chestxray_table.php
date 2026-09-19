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
        Schema::create('dd_chestxray', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('pReferralFacility')->nullable();
            $table->date('pLabDate')->nullable();
            $table->string('pFindings', 11)->nullable();
            $table->text('pRemarksFindings')->nullable();
            $table->string('pObservation', 11)->nullable();
            $table->text('pRemarksObservation')->nullable();
            $table->date('pDateAdded')->nullable();
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
        Schema::dropIfExists('dd_chestxray');
    }
};
