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
        Schema::create('dd_profile_ncdqans', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('en_caseno', 21)->nullable();
            $table->string('p_TransNo', 21)->nullable();
            $table->enum('dQid1_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid2_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid3_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid4_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid5_Ynx', ['Y', 'N', 'X'])->nullable()->comment('lib_ncdq, X means Dont know');
            $table->enum('dQid6_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid7_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid8_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid9_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid10_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid11_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid12_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid13_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid14_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid15_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid16_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid17_abcde', ['A', 'B', 'C', 'D', 'E'])->nullable()->comment('lib_ncdq | A: <10% | B: 10–20% | C: 20–30% | D: 30–40% | E: >=40%');
            $table->enum('dQid18_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid19_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->string('dQid19_Fbsmg', 50)->nullable()->comment('Answer for QID19: FBS/RBS in mg/dL');
            $table->string('dQid19_Fbsmmol', 50)->nullable()->comment('Answer for QID19: FBS/RBS in mmol/L');
            $table->date('dQid19_Fbsdate')->nullable();
            $table->enum('dQid20_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->string('dQid20_Choleval', 3)->nullable()->comment('lib_ncdq, Answer for QID20: Total Cholesterol value in mg/dL');
            $table->date('dQid20_Choledate')->nullable()->comment('Answer for QID20: Date when is the FBS/RBS Value was taken');
            $table->enum('dQid21_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->string('dQid21_Ketonval', 3)->nullable()->comment('lib_ncdq, Answer for QID21: Urine Ketones Value');
            $table->date('dQid21_Ketondate')->nullable()->comment('Answer for QID21: Date when is the Urine Ketones Value was taken');
            $table->enum('dQid22_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->string('dQid22_Proteinval', 3)->nullable()->comment('lib_ncdq, Answer for QID22: Urine Protein Value in mg/dL');
            $table->date('dQid22_Proteindate')->nullable()->comment('Answer for QID22: Date when the Urine Protein Value was taken');
            $table->enum('dQid23_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dQid24_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('dReportStatus', ['V', 'U', 'F'])->nullable()->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('dDeficiencyRemarks')->nullable();
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
        Schema::dropIfExists('dd_profile_ncdqans');
    }
};
