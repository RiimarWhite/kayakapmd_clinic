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
        Schema::create('doctors', function (Blueprint $table) {
            $table->integer('doccd')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('doccode', 10)->nullable();
            $table->string('docrefno', 25)->nullable();
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
            $table->boolean('phicenable')->nullable();
            $table->double('phicrate')->nullable();
            $table->double('pfrate')->nullable();
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
            $table->double('tax')->nullable();
            $table->tinyInteger('issuehospOR')->nullable();
            $table->string('expertise', 150)->nullable();
            $table->longText('clinichours')->nullable();
            $table->longText('otherinfo')->nullable();
            $table->longText('biodata')->nullable();
            $table->string('clinicroom', 120)->nullable();
            $table->tinyInteger('quevisible')->nullable();
            $table->string('profgroup', 30)->nullable();
            $table->tinyInteger('autoAddVAT')->nullable();
            $table->double('VAT')->nullable();
            $table->tinyInteger('allowtextresult')->nullable();
            $table->tinyInteger('allowdocsystem')->nullable();
            $table->date('phicexpiry')->nullable();
            $table->date('licnoexpiry')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->string('statusreason', 120)->nullable();
            $table->tinyInteger('vatable')->nullable();
            $table->double('vatrate')->nullable();
            $table->double('rodrate')->nullable();
            $table->string('phicname', 120)->nullable();
            $table->string('department', 120)->nullable();
            $table->string('docfirst', 80)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
