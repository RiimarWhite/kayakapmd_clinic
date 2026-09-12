<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Detailed Comment: Check if 'secretaryrights' table exists and does not already contain
        // 'clientcode' before attempting to alter the table. This handles fresh databases where
        // secretaryrights may not be present or legacy databases where clientcode already exists.
        if (Schema::hasTable('secretaryrights') && !Schema::hasColumn('secretaryrights', 'clientcode')) {
            Schema::table('secretaryrights', function (Blueprint $table) {
                $table->string('clientcode')->nullable()->index();
            });
        }

        // Detailed Comment: Check if 'adminrights' table exists and does not already have 'clientcode'.
        if (Schema::hasTable('adminrights') && !Schema::hasColumn('adminrights', 'clientcode')) {
            Schema::table('adminrights', function (Blueprint $table) {
                $table->string('clientcode')->nullable()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Detailed Comment: Safely drop 'clientcode' column from secretaryrights if present.
        if (Schema::hasTable('secretaryrights') && Schema::hasColumn('secretaryrights', 'clientcode')) {
            Schema::table('secretaryrights', function (Blueprint $table) {
                $table->dropColumn('clientcode');
            });
        }

        // Detailed Comment: Safely drop 'clientcode' column from adminrights if present.
        if (Schema::hasTable('adminrights') && Schema::hasColumn('adminrights', 'clientcode')) {
            Schema::table('adminrights', function (Blueprint $table) {
                $table->dropColumn('clientcode');
            });
        }
    }
};
