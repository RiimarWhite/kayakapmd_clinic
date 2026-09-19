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
        Schema::create('dd_fobt', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('pReferralFacility')->nullable()->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for FOBT ');
            $table->enum('pFindings', ['P', 'N'])->nullable()->comment('P - Positive;
                            N - Negative ');
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
        Schema::dropIfExists('dd_fobt');
    }
};
