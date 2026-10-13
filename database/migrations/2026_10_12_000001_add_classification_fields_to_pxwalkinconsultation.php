<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detailed Comment: Migration to add clinical classification fields (lmp_date and delivery_procedure)
 * to pxwalkinconsultation table for Obstetrics, Gynecology, and New Born patient workflows.
 * Defensive checks (hasTable and hasColumn) are employed to ensure idempotency across diverse environments.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds nullable lmp_date (DATE) and delivery_procedure (VARCHAR(255)) columns to pxwalkinconsultation.
     */
    public function up(): void
    {
        if (Schema::hasTable('pxwalkinconsultation')) {
            Schema::table('pxwalkinconsultation', function (Blueprint $table) {
                // Defensive check before adding lmp_date
                if (!Schema::hasColumn('pxwalkinconsultation', 'lmp_date')) {
                    $table->date('lmp_date')->nullable()->after('died');
                }

                // Defensive check before adding delivery_procedure
                if (!Schema::hasColumn('pxwalkinconsultation', 'delivery_procedure')) {
                    $table->string('delivery_procedure', 255)->nullable()->after('lmp_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     * Safely drops the classification columns if present.
     */
    public function down(): void
    {
        if (Schema::hasTable('pxwalkinconsultation')) {
            Schema::table('pxwalkinconsultation', function (Blueprint $table) {
                if (Schema::hasColumn('pxwalkinconsultation', 'delivery_procedure')) {
                    $table->dropColumn('delivery_procedure');
                }
                if (Schema::hasColumn('pxwalkinconsultation', 'lmp_date')) {
                    $table->dropColumn('lmp_date');
                }
            });
        }
    }
};
