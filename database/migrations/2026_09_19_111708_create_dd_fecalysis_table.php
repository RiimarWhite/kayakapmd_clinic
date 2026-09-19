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
        Schema::create('dd_fecalysis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('pReferralFacility')->nullable()->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for FECALYSIS ');
            $table->enum('pColor', ['1', '2', '3', '4', '5', '6'])->nullable()->comment('1 - Brown;
                        2 - Black;
                        3 - Red;
                        4 - White/Grey;
                        5 - Yellow;
                        6 - Green;
                        ');
            $table->enum('pConsistency', ['1', '2', '3', '4', '5', '6'])->nullable()->comment('1 - Soft;
                        2 - Well-Formed;
                        3 - Semi-Formed;
                        4 - Watery;
                        5 - Mucoid;
                        6 - Hard
                        ');
            $table->string('pRbc', 50)->nullable()->comment('Value for RBC (/hpf)');
            $table->string('pWbc', 50)->nullable()->comment('Value for WBC(/hpf)');
            $table->string('pOva', 50)->nullable()->comment('Value for RBC (=/-)');
            $table->string('pParasite', 50)->nullable()->comment('Value for Parasite (=/-)');
            $table->enum('pBlood', ['P', 'A'])->nullable()->comment('P - Present;
                            A - Absent ');
            $table->string('pPusCells', 50)->nullable()->comment('Value for PUS Cells');
            $table->date('pDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])->nullable()->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->decimal('pDiagnosticLabFee', 10)->nullable();
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
        Schema::dropIfExists('dd_fecalysis');
    }
};
