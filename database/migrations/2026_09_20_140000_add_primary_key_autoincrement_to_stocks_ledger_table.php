<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Detailed Comment: Alters stocks_ledger.id to be BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
     * and sets it as the PRIMARY KEY. Backfills any existing NULL id values with sequential integers first.
     * Implements Rule 4 (Dual-Update Schema Synchronization Protocol) per rules.md.
     */
    public function up(): void
    {
        if (Schema::hasTable('stocks_ledger')) {
            // Detailed Comment: Backfill any existing rows where id is NULL before applying PRIMARY KEY constraint
            $nullCount = DB::table('stocks_ledger')->whereNull('id')->count();
            if ($nullCount > 0) {
                $driver = DB::connection()->getDriverName();
                if ($driver === 'mysql') {
                    $maxId = (int) DB::table('stocks_ledger')->max('id');
                    DB::statement("SET @seq := {$maxId};");
                    DB::statement("UPDATE `stocks_ledger` SET `id` = (@seq := @seq + 1) WHERE `id` IS NULL;");
                } else {
                    $records = DB::table('stocks_ledger')->whereNull('id')->get();
                    $seq = (int) DB::table('stocks_ledger')->max('id');
                    foreach ($records as $record) {
                        $seq++;
                        DB::table('stocks_ledger')->whereNull('id')->limit(1)->update(['id' => $seq]);
                    }
                }
            }

            if (DB::connection()->getDriverName() === 'mysql') {
                // Check if primary key is already present on stocks_ledger
                $primaryKeyExists = DB::select("SHOW KEYS FROM `stocks_ledger` WHERE Key_name = 'PRIMARY'");
                if (empty($primaryKeyExists)) {
                    DB::statement("ALTER TABLE `stocks_ledger` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`);");
                } else {
                    DB::statement("ALTER TABLE `stocks_ledger` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     * Detailed Comment: Reverts stocks_ledger.id back to nullable BIGINT and drops the primary key constraint.
     */
    public function down(): void
    {
        if (Schema::hasTable('stocks_ledger')) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $primaryKeyExists = DB::select("SHOW KEYS FROM `stocks_ledger` WHERE Key_name = 'PRIMARY'");
                if (!empty($primaryKeyExists)) {
                    DB::statement("ALTER TABLE `stocks_ledger` MODIFY COLUMN `id` BIGINT NULL, DROP PRIMARY KEY;");
                } else {
                    DB::statement("ALTER TABLE `stocks_ledger` MODIFY COLUMN `id` BIGINT NULL;");
                }
            }
        }
    }
};
