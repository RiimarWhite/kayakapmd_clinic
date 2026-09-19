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
        Schema::create('hci_profile', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hci_accre_no', 9)->nullable();
            $table->string('hci_username', 30)->nullable();
            $table->string('hci_password', 30)->nullable();
            $table->string('company_name');
            $table->string('company_brgy')->nullable();
            $table->string('company_mun')->nullable();
            $table->string('company_prov')->nullable();
            $table->string('company_region')->nullable();
            $table->string('company_zipcode')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_mobilenumber', 30)->nullable();
            $table->string('company_telephone', 30)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hci_profile');
    }
};
