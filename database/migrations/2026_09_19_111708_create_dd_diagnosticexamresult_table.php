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
        Schema::create('dd_diagnosticexamresult', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pHciCaseNo', 21)->index('dd_diagnosticexamresult_phcicaseno_foreign');
            $table->string('pHciTransNo', 21)->index('dd_diagnosticexamresult_phcitransno_foreign');
            $table->string('pPatientPin', 12);
            $table->enum('pPatientType', ['MM', 'DD']);
            $table->string('pMemPin', 12);
            $table->string('pEffYear', 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_diagnosticexamresult');
    }
};
