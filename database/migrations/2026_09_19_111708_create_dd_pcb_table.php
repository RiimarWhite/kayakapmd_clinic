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
        Schema::create('dd_pcb', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pUsername', 30);
            $table->string('pPassword', 30);
            $table->string('pHciAccreNo', 9);
            $table->string('pPMCCNo', 6);
            $table->integer('pEnlistTotalCnt');
            $table->integer('pProfileTotalCnt');
            $table->integer('pSoapTotalCnt');
            $table->string('pCertificationId', 21);
            $table->string('pHciTransmittalNumber', 21);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_pcb');
    }
};
