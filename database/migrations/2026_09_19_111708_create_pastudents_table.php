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
        Schema::create('pastudents', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->integer('schoolcode')->nullable();
            $table->enum('studenttype', ['STUDENT', 'ALLUMNI', 'DEACTIVATED'])->nullable();
            $table->string('studentidno', 50)->nullable();
            $table->string('oldstudentidno', 50)->nullable();
            $table->string('studentcode', 150)->nullable();
            $table->string('applicantcodeid', 50)->nullable();
            $table->string('alias', 50)->nullable();
            $table->string('studentname', 50)->nullable();
            $table->string('firstname', 50)->nullable();
            $table->string('lastname', 50)->nullable();
            $table->string('midname', 50)->nullable();
            $table->enum('sex', ['MALE', 'FEMALE'])->nullable();
            $table->string('emailadd', 120)->nullable();
            $table->string('cpno', 15)->nullable();
            $table->string('username', 50)->nullable();
            $table->string('passcode', 255)->nullable();
            $table->tinyInteger('reset')->nullable();
            $table->enum('access_status', ['PENDING', 'DEACTIVATED', 'LOCK', 'ACTIVE', 'DEFERRED', 'FOR ACTIVATION'])->nullable();
            $table->dateTime('lastlogin')->nullable();
            $table->dateTime('updated')->nullable();
            $table->string('station', 50)->nullable();
            $table->string('programcategory', 50)->nullable();
            $table->string('programcode', 30)->nullable();
            $table->string('programdscr', 120)->nullable();
            $table->string('year_level', 10)->nullable();
            $table->string('courseyear', 50)->nullable();
            $table->string('enrolleecode', 50)->nullable();
            $table->enum('enrolleetype', ['REGULAR', 'IRREGULAR'])->nullable();
            $table->enum('registrationtype', ['NEW ENROLEE', 'OLD STUDENT', 'TRANSFEREE'])->nullable();
            $table->enum('entrytpe', ['AUTOMATED', 'MANUAL'])->nullable();
            $table->string('accessgrantedby', 80)->nullable();
            $table->dateTime('accessgranted')->nullable();
            $table->string('prospectus', 50)->nullable();
            $table->enum('dataentry_status', ['PENDING', 'VERIFIED'])->nullable();
            $table->date('user_expirydate')->nullable();
            $table->dateTime('user_passwordupdated')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('birthplace', 80)->nullable();
            $table->string('parentsguardian', 180)->nullable();
            $table->string('passkeyprovided', 50)->nullable();
            $table->integer('email_otp')->nullable();
            $table->tinyInteger('email_verified')->nullable();
            $table->dateTime('email_otp_expiry')->nullable();
            $table->integer('cpno_otp')->nullable();
            $table->tinyInteger('cp_verified')->nullable();
            $table->dateTime('cp_otp_expiry')->nullable();
            $table->string('deferred_reason', 200)->nullable();
            $table->enum('studentid_status', ['NO ATTACHMENT', 'ATTACHED', 'VERIFIED', 'REJECTED'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pastudents');
    }
};
