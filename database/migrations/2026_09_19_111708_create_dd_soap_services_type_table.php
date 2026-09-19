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
        Schema::create('dd_soap_services_type', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('services_type', 50)->nullable()->comment('Consultation\',\'Laboratory\',\'Medication\',\'Procedure\',\'Imaging');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_soap_services_type');
    }
};
