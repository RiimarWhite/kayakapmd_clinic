<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detailed Comment: Migration adding HMO Letter of Authorization (LOA) reference number,
 * PhilHealth Yakap coverage flag, and patient co-pay amount to pxsettlements table.
 * Includes defensive idempotency checks for reliable rollback and migration execution.
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
                if (!Schema::hasColumn('pxsettlements', 'hmo_loa_no')) {
                    $table->string('hmo_loa_no', 100)->nullable()->after('hmocode')->comment('HMO Letter of Authorization (LOA) approval / reference number');
                }
                if (!Schema::hasColumn('pxsettlements', 'is_philhealth_yakap')) {
                    $table->boolean('is_philhealth_yakap')->default(0)->nullable()->after('payment_cash')->comment('Flag indicating charge to PhilHealth Yakap primary care coverage');
                }
                if (!Schema::hasColumn('pxsettlements', 'copay')) {
                    $table->double('copay', 11, 2)->default(0.00)->nullable()->after('is_philhealth_yakap')->comment('Patient copayment balance after PhilHealth Yakap / primary care coverage');
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
                if (Schema::hasColumn('pxsettlements', 'copay')) {
                    $table->dropColumn('copay');
                }
                if (Schema::hasColumn('pxsettlements', 'is_philhealth_yakap')) {
                    $table->dropColumn('is_philhealth_yakap');
                }
                if (Schema::hasColumn('pxsettlements', 'hmo_loa_no')) {
                    $table->dropColumn('hmo_loa_no');
                }
            });
        }
    }
};
