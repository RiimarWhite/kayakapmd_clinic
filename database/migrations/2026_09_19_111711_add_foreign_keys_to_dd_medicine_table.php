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
        Schema::table('dd_medicine', function (Blueprint $table) {
            $table->foreign(['pHciCaseNo'])->references(['pHciCaseNo'])->on('dd_enlistment')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pHciTransNo'])->references(['pHciTransNo'])->on('dd_soap_consultation')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dd_medicine', function (Blueprint $table) {
            $table->dropForeign('dd_medicine_phcicaseno_foreign');
            $table->dropForeign('dd_medicine_phcitransno_foreign');
        });
    }
};
