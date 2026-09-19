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
        Schema::create('dd_pespecific', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('pSkinRem')->nullable();
            $table->text('pHeentRem')->nullable();
            $table->text('pChestRem')->nullable();
            $table->text('pHeartRem')->nullable();
            $table->text('pAbdomenRem')->nullable();
            $table->text('pNeuroRem')->nullable();
            $table->text('pRectalRem')->nullable();
            $table->text('pGuRem')->nullable();
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
        Schema::dropIfExists('dd_pespecific');
    }
};
