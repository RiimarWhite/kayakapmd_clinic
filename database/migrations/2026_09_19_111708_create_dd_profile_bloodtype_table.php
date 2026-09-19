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
        Schema::create('dd_profile_bloodtype', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('px_pin', 50)->nullable();
            $table->string('p_TransNo', 21)->nullable();
            $table->string('en_caseno', 21)->nullable();
            $table->enum('dBloodType', ['A+', 'B+', 'AB+', 'O+', 'A-', 'B-', 'AB-', 'O-', ''])->nullable();
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
        Schema::dropIfExists('dd_profile_bloodtype');
    }
};
