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
        Schema::create('dd_sputum', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('pReferralFacility')->nullable();
            $table->date('pLabDate')->nullable();
            $table->enum('pDataCollection', ['1', '2', '3', 'X'])->nullable()->default('X');
            $table->enum('pFindings', ['1', '2'])->nullable();
            $table->text('pRemarks')->nullable();
            $table->string('pNoPlusses', 5)->nullable();
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
        Schema::dropIfExists('dd_sputum');
    }
};
