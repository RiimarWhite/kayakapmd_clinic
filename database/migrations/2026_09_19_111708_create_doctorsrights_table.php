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
        Schema::create('doctorsrights', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('docrefno', 25)->nullable();
            $table->string('doclname', 25)->nullable();
            $table->string('docfname', 35)->nullable();
            $table->string('docmname', 25)->nullable();
            $table->string('suffix', 3)->nullable();
            $table->string('titlename', 5)->nullable();
            $table->string('username', 25)->nullable();
            $table->string('pass', 255)->nullable();
            $table->string('eadd', 25)->nullable();
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
            $table->float('consultationfee', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctorsrights');
    }
};
