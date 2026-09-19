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
        Schema::table('dd_soap_consultation', function (Blueprint $table) {
            $table->foreign(['pHciCaseNo'])->references(['pHciCaseNo'])->on('dd_enlistment')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dd_soap_consultation', function (Blueprint $table) {
            $table->dropForeign('dd_soap_consultation_phcicaseno_foreign');
        });
    }
};
