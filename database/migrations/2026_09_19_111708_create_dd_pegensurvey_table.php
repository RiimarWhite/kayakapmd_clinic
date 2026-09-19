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
        Schema::create('dd_pegensurvey', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('pGenSurveyId', ['1', '2'])->nullable()->comment('1 - Awake and Alert, 2 - Altered Sensorium');
            $table->text('pGenSurveyRem')->nullable();
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
        Schema::dropIfExists('dd_pegensurvey');
    }
};
