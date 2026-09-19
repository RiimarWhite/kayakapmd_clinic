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
        Schema::create('xml_transmittal_reports', function (Blueprint $table) {
            $table->tinyInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('accre_no', 9)->nullable()->comment('Accreditation Number');
            $table->string('report_code', 27)->nullable();
            $table->enum('trans_type', ['INDIVIDUAL', 'BATCH'])->nullable();
            $table->enum('tranche_type', ['FIRST', 'SECOND'])->nullable();
            $table->date('date_range_start')->comment('Date range of generated report');
            $table->date('date_range_end')->nullable()->comment('Date range of generated report');
            $table->date('date_generated')->comment('Date Generated');
            $table->longText('XML_CONTENT')->nullable()->comment('XML Content');
            $table->longText('ENCRYPTED_CONTENT')->nullable()->comment('Content Encrypted');
            $table->string('verifiedby', 80)->nullable();
            $table->enum('status', ['PENDING', 'FOR TRANSMITTAL', 'TRANSMITTED'])->nullable();
            $table->dateTime('transmittal_date')->nullable();
            $table->string('transmittal_refno', 80)->nullable();
            $table->string('transmittedby', 80)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xml_transmittal_reports');
    }
};
