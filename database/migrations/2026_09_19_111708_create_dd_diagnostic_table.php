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
        Schema::create('dd_diagnostic', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pDiagnosticId', 11)->nullable();
            $table->text('pOthRemarks')->nullable();
            $table->enum('pIsPhysicianRecommend', ['Y', 'N', 'X']);
            $table->enum('pPatientRemarks', ['RQ', 'RF', 'XX']);
            $table->enum('pReportStatus', ['V', 'U', 'F'])->default('U');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_diagnostic');
    }
};
