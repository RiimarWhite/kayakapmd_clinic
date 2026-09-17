<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Detailed Comment: Creates or repairs 'doctorsrights', 'secretaryrights', and 'doctors' tables.
     * If running in SQLite (e.g. tests) or a fresh database without pre-existing tables, creates them cleanly.
     * If running against an existing MySQL database, repairs primary keys, auto_increment attributes,
     * null IDs, and ensures 'username' and 'clientcode' columns are properly indexed and populated.
     */
    public function up(): void
    {
        // 1. doctorsrights table
        if (!Schema::hasTable('doctorsrights')) {
            Schema::create('doctorsrights', function (Blueprint $table) {
                $table->increments('id');
                $table->string('dw_clientcode', 12)->nullable();
                $table->string('docrefno', 25)->nullable()->index();
                $table->string('doclname', 25)->nullable()->index();
                $table->string('docfname', 35)->nullable();
                $table->string('docmname', 25)->nullable();
                $table->string('suffix', 3)->nullable();
                $table->string('titlename', 5)->nullable();
                $table->string('username', 25)->nullable()->index();
                $table->string('pass', 255)->nullable();
                $table->string('eadd', 100)->nullable()->index();
                $table->string('mnumber', 15)->nullable();
                $table->string('tin', 50)->nullable();
                $table->string('address', 200)->nullable();
                $table->string('slcode', 25)->nullable();
                $table->string('taxpercent', 25)->nullable();
                $table->string('bankacct', 50)->nullable();
                $table->integer('updateID')->nullable();
                $table->timestamp('updated')->nullable();
                $table->string('updatedby', 50)->nullable();
                $table->integer('Adminsys')->nullable();
                $table->integer('mobileapp')->nullable();
                $table->integer('logged')->nullable();
                $table->string('status', 20)->nullable();
                $table->string('expertise', 100)->nullable();
                $table->string('proftype', 100)->nullable();
                $table->tinyInteger('doctype')->nullable();
                $table->tinyInteger('docmgmt')->nullable();
                $table->tinyInteger('logged_in')->nullable();
                $table->float('consultationfee', 10, 2)->nullable();
            });
        } else {
            // Assign sequential integer IDs for any doctors with NULL id
            $nullDocs = DB::table('doctorsrights')->whereNull('id')->get();
            if ($nullDocs->isNotEmpty()) {
                $maxId = (int) (DB::table('doctorsrights')->max('id') ?? 0);
                foreach ($nullDocs as $doc) {
                    $maxId++;
                    DB::table('doctorsrights')
                        ->where('docrefno', $doc->docrefno)
                        ->update(['id' => $maxId]);
                }
            }

            // Check if primary key already exists (MySQL specific check)
            if (DB::getDriverName() === 'mysql') {
                $primaryKey = DB::select("SHOW KEYS FROM doctorsrights WHERE Key_name = 'PRIMARY'");
                if (empty($primaryKey)) {
                    DB::statement("ALTER TABLE `doctorsrights` MODIFY `id` INT NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
                }
            }
        }

        // 2. secretaryrights table
        if (!Schema::hasTable('secretaryrights')) {
            Schema::create('secretaryrights', function (Blueprint $table) {
                $table->increments('id');
                $table->string('secrefno', 100)->nullable()->index();
                $table->string('secidno', 100)->nullable()->index();
                $table->string('username', 100)->nullable()->index();
                $table->string('secpassword', 255)->nullable();
                $table->string('secfname', 100)->nullable();
                $table->string('secmname', 100)->nullable();
                $table->string('seclname', 100)->nullable()->index();
                $table->string('secsuffix', 10)->nullable();
                $table->date('secbday')->nullable();
                $table->string('secgender', 10)->nullable();
                $table->text('secadrs')->nullable();
                $table->string('seccontactno', 20)->nullable();
                $table->string('secemail', 100)->nullable()->index();
                $table->string('recordedby', 100)->nullable();
                $table->dateTime('recordeddate')->nullable();
                $table->tinyInteger('logged')->nullable();
                $table->dateTime('verifieddate')->nullable();
                $table->tinyInteger('verified')->nullable();
                $table->string('clientcode', 191)->nullable();
            });
        } else {
            // Assign sequential integer IDs for any secretaries with NULL id
            $nullSecs = DB::table('secretaryrights')->whereNull('id')->get();
            if ($nullSecs->isNotEmpty()) {
                $maxId = (int) (DB::table('secretaryrights')->max('id') ?? 0);
                foreach ($nullSecs as $sec) {
                    $maxId++;
                    DB::table('secretaryrights')
                        ->where('secrefno', $sec->secrefno)
                        ->update(['id' => $maxId]);
                }
            }

            // Check if primary key already exists (MySQL specific check)
            if (DB::getDriverName() === 'mysql') {
                $primaryKeySec = DB::select("SHOW KEYS FROM secretaryrights WHERE Key_name = 'PRIMARY'");
                if (empty($primaryKeySec)) {
                    DB::statement("ALTER TABLE `secretaryrights` MODIFY `id` INT NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
                }
            }

            // Add username column if missing
            if (!Schema::hasColumn('secretaryrights', 'username')) {
                Schema::table('secretaryrights', function (Blueprint $table) {
                    $table->string('username', 100)->nullable()->index()->after('secidno');
                });

                if (DB::getDriverName() === 'mysql') {
                    DB::statement("UPDATE `secretaryrights` SET `username` = LOWER(`seclname`) WHERE `username` IS NULL AND `seclname` IS NOT NULL");
                }
            }
        }

        // 3. doctors table (doctor profiles)
        if (!Schema::hasTable('doctors')) {
            Schema::create('doctors', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('doccd')->nullable();
                $table->string('dw_clientcode', 12)->nullable();
                $table->string('doccode', 10)->nullable();
                $table->string('docrefno', 25)->nullable()->index();
                $table->string('doclname', 25)->nullable();
                $table->string('docfname', 35)->nullable();
                $table->string('docmname', 25)->nullable();
                $table->string('suffix', 3)->nullable();
                $table->string('titlename', 5)->nullable();
                $table->string('docname', 70)->nullable();
                $table->string('adrs', 70)->nullable();
                $table->string('emailadd', 80)->nullable();
                $table->string('S2no', 20)->nullable();
                $table->string('PTR', 20)->nullable();
                $table->string('Licno', 20)->nullable();
                $table->string('phicno', 30)->nullable();
                $table->string('tin', 30)->nullable();
                $table->tinyInteger('phicenable')->nullable();
                $table->double('phicrate', 11, 2)->nullable();
                $table->double('pfrate', 11, 2)->nullable();
                $table->dateTime('lastupdate')->nullable();
                $table->string('proftype', 50)->nullable();
                $table->integer('disabletext')->nullable();
                $table->string('cellno', 11)->nullable();
                $table->string('catg', 3)->nullable();
                $table->integer('recid')->nullable();
                $table->string('recby', 50)->nullable();
                $table->string('station', 50)->nullable();
                $table->string('groupname', 3)->nullable();
                $table->string('coacode', 20)->nullable();
                $table->string('accountno', 50)->nullable();
                $table->double('tax', 11, 2)->nullable();
                $table->tinyInteger('issuehospOR')->nullable();
                $table->string('expertise', 150)->nullable();
                $table->longText('clinichours')->nullable();
                $table->longText('otherinfo')->nullable();
                $table->longText('biodata')->nullable();
                $table->string('clinicroom', 120)->nullable();
                $table->tinyInteger('quevisible')->nullable();
                $table->string('profgroup', 30)->nullable();
                $table->tinyInteger('autoAddVAT')->nullable();
                $table->double('VAT', 11, 2)->nullable();
                $table->tinyInteger('allowtextresult')->nullable();
                $table->tinyInteger('allowdocsystem')->nullable();
                $table->date('phicexpiry')->nullable();
                $table->date('licnoexpiry')->nullable();
                $table->tinyInteger('status')->nullable();
                $table->string('statusreason', 120)->nullable();
                $table->tinyInteger('vatable')->nullable();
                $table->double('vatrate', 11, 2)->nullable();
                $table->double('rodrate', 11, 2)->nullable();
                $table->string('phicname', 120)->nullable();
                $table->string('department', 120)->nullable();
                $table->string('docfirst', 80)->nullable();
            });
        }

        // 4. secretary_doctor table
        if (!Schema::hasTable('secretary_doctor')) {
            Schema::create('secretary_doctor', function (Blueprint $table) {
                $table->increments('id');
                $table->string('docrefno', 60)->nullable()->index();
                $table->string('secrefno', 60)->nullable()->index();
                $table->string('recordedby', 60)->nullable();
                $table->dateTime('recordeddate')->nullable();
                $table->tinyInteger('active')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     * Detailed Comment: Safely reverts added columns or drops tables if created fresh.
     */
    public function down(): void
    {
        if (Schema::hasTable('secretaryrights') && Schema::hasColumn('secretaryrights', 'username')) {
            Schema::table('secretaryrights', function (Blueprint $table) {
                $table->dropColumn('username');
            });
        }
    }
};
