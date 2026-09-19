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
        Schema::create('pcb', function (Blueprint $table) {
            $table->string('clientcode', 12)->nullable();
            $table->string('userid', 30)->nullable()->comment('to be provided by phic - pusername');
            $table->string('passwd', 30)->nullable()->comment('to be provided by phic pPassword');
            $table->string('hciaccreno', 9)->nullable();
            $table->string('pmccno', 6)->nullable();
            $table->float('enlistTotalcnt')->nullable();
            $table->float('profileTotalcnt')->nullable();
            $table->float('soapTotalcnt')->nullable();
            $table->string('certificationid', 21)->nullable();
            $table->string('hcitransmittalnumber', 21)->nullable();
            $table->string('facilityName', 120)->nullable();
            $table->string('region', 50)->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('updated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pcb');
    }
};
