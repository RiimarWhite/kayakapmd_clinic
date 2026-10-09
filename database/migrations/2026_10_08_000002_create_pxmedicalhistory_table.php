<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detailed Comment: Migration establishing pxmedicalhistory table for permanent patient
 * clinical medical history (allergies such as seafood or penicillin, injection/immunization log,
 * past illnesses, surgeries, maintenance medications, and family history), distinct from per-visit consultations.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pxmedicalhistory')) {
            Schema::create('pxmedicalhistory', function (Blueprint $table) {
                $table->id();
                $table->string('pxrefno', 50)->index()->comment('Patient master reference number matching pxmasterlist.pxrefno');
                $table->string('pincode', 50)->nullable()->index()->comment('Patient PhilHealth or internal PIN matching pxmasterlist.pincode');
                $table->text('allergies')->nullable()->comment('Patient permanent allergies (food e.g. seafood, drug e.g. penicillin, environmental)');
                $table->text('injections_immunization')->nullable()->comment('Patient immunization and injection administration history');
                $table->text('past_medical_history')->nullable()->comment('Past medical illnesses, hypertension, diabetes, asthma, and chronic conditions');
                $table->text('surgical_history')->nullable()->comment('Past surgical procedures and prior hospitalizations');
                $table->text('family_history')->nullable()->comment('Hereditary family diseases and medical history');
                $table->text('maintenance_medications')->nullable()->comment('Ongoing maintenance medications and dosages');
                $table->text('notes')->nullable()->comment('General clinical remarks, precautions, or special medical warnings');
                $table->string('recordedby', 80)->nullable()->comment('Staff/physician who recorded initial medical history entry');
                $table->string('updatedby', 80)->nullable()->comment('Staff/physician who last updated medical history entry');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pxmedicalhistory');
    }
};
