<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detailed Comment: Migration to add clinical consultation narrative history fields
 * (brief_history_present_illness and pertinent_past_medhistory) to pxwalkinconsultation table.
 * These fields capture the patient's History of Present Illness (HPI) and Past Medical History (PMH)
 * at the consultation card level across Secretary and Admin consoles.
 * Defensive checks (hasTable and hasColumn) are employed to ensure idempotency across diverse environments.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds nullable brief_history_present_illness (LONGTEXT) and pertinent_past_medhistory (LONGTEXT)
     * columns to pxwalkinconsultation.
     */
    public function up(): void
    {
        if (Schema::hasTable('pxwalkinconsultation')) {
            Schema::table('pxwalkinconsultation', function (Blueprint $table) {
                // Defensive check before adding brief_history_present_illness
                if (!Schema::hasColumn('pxwalkinconsultation', 'brief_history_present_illness')) {
                    $table->longText('brief_history_present_illness')->nullable()->after('reasonforconsultation');
                }

                // Defensive check before adding pertinent_past_medhistory
                if (!Schema::hasColumn('pxwalkinconsultation', 'pertinent_past_medhistory')) {
                    $table->longText('pertinent_past_medhistory')->nullable()->after('brief_history_present_illness');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     * Safely drops the narrative history columns if present.
     */
    public function down(): void
    {
        if (Schema::hasTable('pxwalkinconsultation')) {
            Schema::table('pxwalkinconsultation', function (Blueprint $table) {
                if (Schema::hasColumn('pxwalkinconsultation', 'pertinent_past_medhistory')) {
                    $table->dropColumn('pertinent_past_medhistory');
                }
                if (Schema::hasColumn('pxwalkinconsultation', 'brief_history_present_illness')) {
                    $table->dropColumn('brief_history_present_illness');
                }
            });
        }
    }
};
