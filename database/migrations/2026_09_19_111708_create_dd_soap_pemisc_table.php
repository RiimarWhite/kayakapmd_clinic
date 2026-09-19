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
        Schema::create('dd_soap_pemisc', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('px_consultcode_cn', 50)->nullable();
            $table->string('s_TransNo', 21)->nullable();
            $table->string('en_caseno', 21)->nullable();
            $table->string('dSkinId', 3)->nullable()->comment('lib_skin_extremities');
            $table->string('dHeentId', 3)->nullable()->comment('lib_heent');
            $table->string('dChestId', 3)->nullable()->comment('lib_chest');
            $table->string('dHeartId', 3)->nullable()->comment('lib_heart');
            $table->string('dAbdomenId', 3)->nullable()->comment('lib_abdomen');
            $table->string('dNeuroId', 3)->nullable()->comment('lib_neuro');
            $table->string('dRectalId', 3)->nullable()->comment('lib_digital_rectal');
            $table->string('dGuId', 3)->nullable()->comment('lib_genitourinary');
            $table->enum('dReportStatus', ['V', 'U', 'F'])->nullable()->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->string('dDeficiencyRemarks', 2000)->nullable();
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
        Schema::dropIfExists('dd_soap_pemisc');
    }
};
