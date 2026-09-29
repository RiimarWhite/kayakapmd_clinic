<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds instructions to stocks_ledger for prescription sig/instructions per item,
     * and adds photo_path to pxmasterlist so patient photos persist across consultations and views.
     */
    public function up(): void
    {
        // 1. Add instructions to stocks_ledger if not present
        if (Schema::hasTable('stocks_ledger') && !Schema::hasColumn('stocks_ledger', 'instructions')) {
            Schema::table('stocks_ledger', function (Blueprint $table) {
                $table->text('instructions')->nullable()->after('remarks')->comment('Prescription instructions / dosage instructions for the medicine');
            });
        }

        // 2. Add photo_path to pxmasterlist if not present
        if (Schema::hasTable('pxmasterlist') && !Schema::hasColumn('pxmasterlist', 'photo_path')) {
            Schema::table('pxmasterlist', function (Blueprint $table) {
                $table->string('photo_path', 255)->nullable()->after('senior_idno')->comment('Path to patient captured/uploaded photo');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stocks_ledger') && Schema::hasColumn('stocks_ledger', 'instructions')) {
            Schema::table('stocks_ledger', function (Blueprint $table) {
                $table->dropColumn('instructions');
            });
        }

        if (Schema::hasTable('pxmasterlist') && Schema::hasColumn('pxmasterlist', 'photo_path')) {
            Schema::table('pxmasterlist', function (Blueprint $table) {
                $table->dropColumn('photo_path');
            });
        }
    }
};
