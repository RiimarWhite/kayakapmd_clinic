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
        Schema::create('dd_soap_diagnostic', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('px_consultcode_cn', 50)->nullable();
            $table->string('s_TransNo', 21)->nullable();
            $table->string('en_caseno', 21)->nullable();
            $table->string('dDiagnosticId', 3)->nullable();
            $table->text('dOthRemarks')->nullable()->comment('500');
            $table->enum('dIsPhysicianRecommend', ['Y', 'N', 'X'])->nullable();
            $table->enum('dPatientRemarks', ['RQ', 'RF', 'XX'])->nullable();
            $table->enum('dReportStatus', ['V', 'U', 'F'])->nullable();
            $table->string('dDeficiencyRemarks', 2000)->nullable();
            $table->string('prodcode', 50)->nullable();
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
        Schema::dropIfExists('dd_soap_diagnostic');
    }
};
