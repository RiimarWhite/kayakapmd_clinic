<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detailed Comment: Migration adding Senior/PWD reference number, PHIC ICD/RVS code,
 * and Other discount description columns to pxsettlements table with idempotent checks.
 * Allows cashier and settlement audit to record reference credentials alongside deduction amounts.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('pxsettlements')) {
            Schema::table('pxsettlements', function (Blueprint $table) {
                if (!Schema::hasColumn('pxsettlements', 'srpwd_refno')) {
                    $table->string('srpwd_refno', 80)->nullable()->after('less_srpwd')->comment('Senior Citizen / PWD identification or reference card number');
                }
                if (!Schema::hasColumn('pxsettlements', 'phic_icd_rvs')) {
                    $table->string('phic_icd_rvs', 100)->nullable()->after('less_phic')->comment('PhilHealth ICD-10 diagnosis or RVS procedure case rate reference');
                }
                if (!Schema::hasColumn('pxsettlements', 'discount_description')) {
                    $table->string('discount_description', 255)->nullable()->after('less_discount')->comment('Description or authorization note for other consultation discount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pxsettlements')) {
            Schema::table('pxsettlements', function (Blueprint $table) {
                if (Schema::hasColumn('pxsettlements', 'discount_description')) {
                    $table->dropColumn('discount_description');
                }
                if (Schema::hasColumn('pxsettlements', 'phic_icd_rvs')) {
                    $table->dropColumn('phic_icd_rvs');
                }
                if (Schema::hasColumn('pxsettlements', 'srpwd_refno')) {
                    $table->dropColumn('srpwd_refno');
                }
            });
        }
    }
};
