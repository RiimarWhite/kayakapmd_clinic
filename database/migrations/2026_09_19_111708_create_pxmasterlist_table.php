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
        Schema::create('pxmasterlist', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('pxrefno', 50)->nullable();
            $table->string('pincode', 21)->nullable();
            $table->string('patientname', 180)->nullable();
            $table->string('pxlastname', 80)->nullable();
            $table->string('pxfirstname', 80)->nullable();
            $table->string('pxmidname', 80)->nullable();
            $table->string('pxsuffix', 10)->nullable();
            $table->string('phic_pin', 30)->nullable();
            $table->string('ipd_pincode', 50)->nullable()->comment('inpatient pin');
            $table->string('gender', 255)->nullable();
            $table->date('birthday')->nullable();
            $table->double('age')->nullable();
            $table->string('religion', 80)->nullable();
            $table->string('nationality', 80)->nullable();
            $table->string('mobilenumber', 20)->nullable();
            $table->string('emailaddress', 60)->nullable();
            $table->string('address', 300)->nullable();
            $table->string('streetadrs', 80)->nullable();
            $table->string('brgy', 80)->nullable();
            $table->string('muncity', 80)->nullable();
            $table->string('province', 80)->nullable();
            $table->string('zipcode', 10)->nullable();
            $table->string('region', 80)->nullable();
            $table->string('country', 80)->nullable();
            $table->tinyInteger('ispwd')->nullable();
            $table->date('last_consultation')->nullable();
            $table->string('last_enlistcode', 50)->nullable();
            $table->string('last_docrefno', 50)->nullable();
            $table->string('last_docname', 130)->nullable();
            $table->string('classification', 120)->nullable();
            $table->date('followupdate')->nullable();
            $table->boolean('followupcheckup')->nullable();
            $table->string('recordedby', 80)->nullable();
            $table->dateTime('recordeddate')->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('updated')->nullable();
            $table->boolean('canaccess_online')->nullable();
            $table->boolean('allow_emailnotification')->nullable();
            $table->boolean('allow_sms')->nullable();
            $table->string('senior_idno', 80)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pxmasterlist');
    }
};
