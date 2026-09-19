<?php

/**
 * Detailed Comment: Database Migration to update adminrights schema with profile attributes
 * and cleanse corrupt secretary-doctor association records.
 *
 * Implements defensive checks (Schema::hasColumn / Schema::hasTable) per KayakapMD Rule 4.
 * Adds administrative name and contact columns to support unified staff management,
 * purges null docrefno rows from secretary_doctor that cause SQL NOT IN failure,
 * and ensures all secretary accounts possess a valid, non-null secrefno.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Detailed Comment: Adds profile columns to adminrights and cleanses orphan records.
     */
    public function up(): void
    {
        if (Schema::hasTable('adminrights')) {
            Schema::table('adminrights', function (Blueprint $table) {
                if (!Schema::hasColumn('adminrights', 'adminfname')) {
                    $table->string('adminfname', 100)->nullable()->after('username');
                }
                if (!Schema::hasColumn('adminrights', 'adminmname')) {
                    $table->string('adminmname', 100)->nullable()->after('adminfname');
                }
                if (!Schema::hasColumn('adminrights', 'adminlname')) {
                    $table->string('adminlname', 100)->nullable()->after('adminmname');
                }
                if (!Schema::hasColumn('adminrights', 'admincontactno')) {
                    $table->string('admincontactno', 25)->nullable()->after('adminlname');
                }
                if (!Schema::hasColumn('adminrights', 'adminemail')) {
                    $table->string('adminemail', 100)->nullable()->after('admincontactno');
                }
            });
        }

        // Detailed Comment: Remove corrupted secretary-doctor records where docrefno is null or empty.
        // In MySQL, "col NOT IN ('x', NULL)" yields UNKNOWN (empty set) for all rows, breaking doctor assignment.
        if (Schema::hasTable('secretary_doctor')) {
            DB::table('secretary_doctor')
                ->whereNull('docrefno')
                ->orWhere('docrefno', '')
                ->delete();
        }

        // Detailed Comment: Ensure any existing secretary lacking a secrefno receives a unique reference identifier.
        if (Schema::hasTable('secretaryrights')) {
            $secretariesWithoutRef = DB::table('secretaryrights')
                ->whereNull('secrefno')
                ->orWhere('secrefno', '')
                ->get();

            foreach ($secretariesWithoutRef as $sec) {
                $generatedRef = now()->format('mdYHis') . 'TASK' . str_pad($sec->id ?? 1, 3, '0', STR_PAD_LEFT);
                DB::table('secretaryrights')
                    ->where('id', $sec->id)
                    ->update(['secrefno' => $generatedRef]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * Detailed Comment: Drops newly added profile columns from adminrights.
     */
    public function down(): void
    {
        if (Schema::hasTable('adminrights')) {
            Schema::table('adminrights', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('adminrights', 'adminemail')) {
                    $columnsToDrop[] = 'adminemail';
                }
                if (Schema::hasColumn('adminrights', 'admincontactno')) {
                    $columnsToDrop[] = 'admincontactno';
                }
                if (Schema::hasColumn('adminrights', 'adminlname')) {
                    $columnsToDrop[] = 'adminlname';
                }
                if (Schema::hasColumn('adminrights', 'adminmname')) {
                    $columnsToDrop[] = 'adminmname';
                }
                if (Schema::hasColumn('adminrights', 'adminfname')) {
                    $columnsToDrop[] = 'adminfname';
                }

                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
