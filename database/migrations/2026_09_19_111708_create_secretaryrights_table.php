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
        Schema::create('secretaryrights', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('secrefno', 100)->nullable();
            $table->string('secidno', 100)->nullable();
            $table->string('username', 100)->nullable()->index();
            $table->string('secpassword', 100)->nullable();
            $table->string('secfname', 100)->nullable();
            $table->string('secmname', 100)->nullable();
            $table->string('seclname', 100)->nullable();
            $table->string('secsuffix', 10)->nullable();
            $table->date('secbday')->nullable();
            $table->string('secgender', 10)->nullable();
            $table->longText('secadrs')->nullable();
            $table->string('seccontactno', 20)->nullable();
            $table->string('secemail', 50)->nullable();
            $table->string('recordedby', 100)->nullable();
            $table->dateTime('recordeddate')->nullable();
            $table->tinyInteger('logged')->nullable();
            $table->dateTime('verifieddate')->nullable();
            $table->tinyInteger('verified')->nullable();
            $table->string('clientcode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secretaryrights');
    }
};
