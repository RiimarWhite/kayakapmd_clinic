<?php

/**
 * Detailed Comment: Database Migration to expand the status ENUM column on pxwalkinconsultation.
 *
 * Implements Rule 4 (Dual-Update Schema Synchronization Protocol) per rules.md.
 * Adds 'FOR_BILLING' to the status enum:
 * ('PENDING', 'FOR CONFIRMATION', 'WAITING', 'IN_CONSULTATION', 'FOR_BILLING', 'COMPLETED', 'CANCELLED', 'NO_SHOW', 'UNSCHEDULED')
 * This enables the OPD Consultation Workflow Plan (.gemini/Opd consultation workflow.md) transition where the doctor
 * finishes the clinical check-up and hands the record back to the secretary for billing and document release.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Detailed Comment: Alters status column on pxwalkinconsultation to include 'FOR_BILLING' enum choice.
     */
    public function up(): void
    {
        if (Schema::hasTable('pxwalkinconsultation')) {
            // Detailed Comment: Execute raw ALTER TABLE only on MySQL as SQLite in tests lacks MODIFY COLUMN syntax
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `pxwalkinconsultation` MODIFY COLUMN `status` ENUM('PENDING', 'FOR CONFIRMATION', 'WAITING', 'IN_CONSULTATION', 'FOR_BILLING', 'COMPLETED', 'CANCELLED', 'NO_SHOW', 'UNSCHEDULED') NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     * Detailed Comment: Reverts status enum back to previous options, safely transitioning any FOR_BILLING rows to IN_CONSULTATION.
     */
    public function down(): void
    {
        if (Schema::hasTable('pxwalkinconsultation')) {
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::table('pxwalkinconsultation')
                    ->where('status', 'FOR_BILLING')
                    ->update(['status' => 'IN_CONSULTATION']);

                DB::statement("ALTER TABLE `pxwalkinconsultation` MODIFY COLUMN `status` ENUM('PENDING', 'FOR CONFIRMATION', 'WAITING', 'IN_CONSULTATION', 'COMPLETED', 'CANCELLED', 'NO_SHOW', 'UNSCHEDULED') NULL");
            }
        }
    }
};
