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
        Schema::create('dd_diag_examresult_master', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('specmen_barcode', 80)->nullable();
            $table->string('transcode', 50)->nullable();
            $table->enum('report_group', ['DIAGNOSTIC', 'IMAGING', 'OTHERS'])->nullable()->comment('Diagnostoc = L / Imaging  = X / Others = O');
            $table->string('result_reportcode', 50)->nullable();
            $table->bigInteger('result_reportno')->nullable();
            $table->enum('entry_source', ['CLINIC', 'YAKAP'])->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('px_consultcode_cn', 50)->nullable();
            $table->string('en_CaseNo', 21)->nullable();
            $table->string('s_TransNo', 21)->nullable();
            $table->string('dPatientPin', 12)->nullable();
            $table->enum('dPatientType', ['MM', 'DD'])->nullable();
            $table->string('dMemPin', 12)->nullable();
            $table->string('dEffYear', 4)->nullable();
            $table->enum('pstatus', ['D', 'N', 'X', 'W', 'V'])->nullable()->comment('V = VOID');
            $table->string('prodcode', 50)->nullable();
            $table->string('phic_reference_code', 50)->nullable();
            $table->string('item_dscr', 220)->nullable();
            $table->string('px_name', 120)->nullable();
            $table->string('adress', 220)->nullable();
            $table->date('dob')->nullable();
            $table->double('age')->nullable();
            $table->enum('sex', ['MALE', 'FEMALE'])->nullable();
            $table->string('doccode', 50)->nullable();
            $table->string('docname', 80)->nullable();
            $table->longText('remarks')->nullable();
            $table->string('extractedby', 120)->nullable();
            $table->dateTime('extracted')->nullable();
            $table->string('resultcreatedby', 120)->nullable();
            $table->dateTime('resultcreated')->nullable();
            $table->string('medtech_name', 120)->nullable();
            $table->string('medtech_licno', 80)->nullable();
            $table->string('createdby', 80)->nullable();
            $table->dateTime('created')->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('updated')->nullable();
            $table->string('orno', 0)->nullable();
            $table->date('ordate')->nullable();
            $table->string('emailadd', 80)->nullable();
            $table->string('normal_value_refcode', 0)->nullable();
            $table->enum('entry_type', ['PHIC', 'INTERNAL'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_diag_examresult_master');
    }
};
