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
        Schema::create('dd_pemisc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pSkinId', 3)->nullable()->comment('lib_skin_extremities');
            $table->string('pHeentId', 3)->nullable()->comment('lib_heent');
            $table->string('pChestId', 3)->nullable()->comment('lib_chest');
            $table->string('pHeartId', 3)->nullable()->comment('lib_heart');
            $table->string('pAbdomenId', 3)->nullable()->comment('lib_abdomen');
            $table->string('pNeuroId', 3)->nullable()->comment('lib_neuro');
            $table->string('pRectalId', 3)->nullable()->comment('lib_digital_rectal');
            $table->string('pGuId', 3)->nullable()->comment('lib_genitourinary');
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
        Schema::dropIfExists('dd_pemisc');
    }
};
