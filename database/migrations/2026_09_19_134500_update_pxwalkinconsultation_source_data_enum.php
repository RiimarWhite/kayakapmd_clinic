<?php

/**
 * Detailed Comment: Database Migration to expand the source_data ENUM column on pxwalkinconsultation.
 *
 * Implements Rule 4 (Dual-Update Schema Synchronization Protocol) per rules.md.
 * Adds 'ADMIN' to the source_data enum ('ONLINE', 'QUELINE', 'SECRETARY', 'DOCTOR', 'ADMIN')
 * allowing consultations initiated directly by administrators from the admin/secretary console
 * to persist cleanly without MySQL 1265 "Data truncated for column 'source_data'" warnings/exceptions.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Detailed Comment: Alters source_data column on pxwalkinconsultation to include 'ADMIN' enum value.
     */
    public function up(): void
    {
        if (Schema::hasTable('pxwalkinconsultation')) {
            // Detailed Comment: Execute raw ALTER TABLE only on MySQL as SQLite in tests lacks MODIFY COLUMN syntax
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `pxwalkinconsultation` MODIFY COLUMN `source_data` ENUM('ONLINE', 'QUELINE', 'SECRETARY', 'DOCTOR', 'ADMIN') NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     * Detailed Comment: Reverts source_data enum back to the original four choices.
     */
    public function down(): void
    {
        if (Schema::hasTable('pxwalkinconsultation')) {
            if (DB::connection()->getDriverName() === 'mysql') {
                // Cleanse any 'ADMIN' rows to 'SECRETARY' before shrinking enum to prevent data truncation
                DB::table('pxwalkinconsultation')
                    ->where('source_data', 'ADMIN')
                    ->update(['source_data' => 'SECRETARY']);

                DB::statement("ALTER TABLE `pxwalkinconsultation` MODIFY COLUMN `source_data` ENUM('ONLINE', 'QUELINE', 'SECRETARY', 'DOCTOR') NULL");
            }
        }
    }
};
