<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detailed Comment: Migration to add group_code, status, and created_by metadata columns
 * to stocks_groupings table with defensive, idempotent checks.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('stocks_groupings')) {
            Schema::table('stocks_groupings', function (Blueprint $table) {
                if (!Schema::hasColumn('stocks_groupings', 'group_code')) {
                    $table->string('group_code', 50)->nullable()->index()->after('id');
                }
                if (!Schema::hasColumn('stocks_groupings', 'status')) {
                    $table->string('status', 20)->default('ACTIVE')->after('description');
                }
                if (!Schema::hasColumn('stocks_groupings', 'created_by')) {
                    $table->string('created_by', 50)->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stocks_groupings')) {
            Schema::table('stocks_groupings', function (Blueprint $table) {
                if (Schema::hasColumn('stocks_groupings', 'created_by')) {
                    $table->dropColumn('created_by');
                }
                if (Schema::hasColumn('stocks_groupings', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('stocks_groupings', 'group_code')) {
                    $table->dropColumn('group_code');
                }
            });
        }
    }
};
