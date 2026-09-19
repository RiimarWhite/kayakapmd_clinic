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
        Schema::create('dd_ncdqans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('pQid1_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid2_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid3_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid4_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid5_Yn', ['Y', 'N', 'X'])->nullable()->comment('lib_ncdq, X means Dont know');
            $table->enum('pQid6_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid7_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid8_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid9_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid10_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid11_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid12_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid13_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid14_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid15_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid16_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid17_Yn', ['A', 'B', 'C', 'D', 'E'])->nullable()->comment('lib_ncdq | A: <10% | B: 10–20% | C: 20–30% | D: 30–40% | E: >=40%');
            $table->enum('pQid18_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid19_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->string('pQid19_Fbsmg', 50)->nullable()->comment('Answer for QID19: FBS/RBS in mg/dL');
            $table->string('pQid19_Fbsmmol', 50)->nullable()->comment('Answer for QID19: FBS/RBS in mmol/L');
            $table->date('pQid19_Fbsdate')->nullable();
            $table->enum('pQid20_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->string('pQid20_Choleval', 3)->nullable()->comment('lib_ncdq, Answer for QID20: Total Cholesterol value in mg/dL');
            $table->date('pQid20_Choledate')->nullable()->comment('Answer for QID20: Date when is the FBS/RBS Value was taken');
            $table->enum('pQid21_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->string('pQid21_Ketonval', 3)->nullable()->comment('lib_ncdq, Answer for QID21: Urine Ketones Value');
            $table->date('pQid21_Ketondate')->nullable()->comment('Answer for QID21: Date when is the Urine Ketones Value was taken');
            $table->enum('pQid22_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->string('pQid22_Proteinval', 3)->nullable()->comment('lib_ncdq, Answer for QID22: Urine Protein Value in mg/dL');
            $table->date('pQid22_Proteindate')->nullable()->comment('Answer for QID22: Date when the Urine Protein Value was taken');
            $table->enum('pQid23_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
            $table->enum('pQid24_Yn', ['Y', 'N'])->nullable()->comment('lib_ncdq');
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
        Schema::dropIfExists('dd_ncdqans');
    }
};
