<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Detailed Comment: Adds cta_type column to pxsettlements table with idempotency checks
     * to record card transaction type (e.g., cc for Credit Card, dc for Debit Card).
     */
    public function up(): void
    {
        if (Schema::hasTable('pxsettlements')) {
            Schema::table('pxsettlements', function (Blueprint $table) {
                if (!Schema::hasColumn('pxsettlements', 'cta_type')) {
                    $table->string('cta_type', 50)->nullable()->after('payment_card');
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
                if (Schema::hasColumn('pxsettlements', 'cta_type')) {
                    $table->dropColumn('cta_type');
                }
            });
        }
    }
};
