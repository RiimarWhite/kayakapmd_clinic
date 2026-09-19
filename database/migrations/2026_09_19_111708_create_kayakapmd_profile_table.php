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
        Schema::create('kayakapmd_profile', function (Blueprint $table) {
            $table->string('clientcode', 12)->nullable()->comment('assign code unque for everyclient');
            $table->string('facility_type', 30)->nullable()->comment('Hospital, Ambulatory, clinic, lgu');
            $table->string('reports_address', 200)->nullable();
            $table->string('reports_citymunprov', 200)->nullable();
            $table->string('reports_contactnumber', 200)->nullable();
            $table->string('EMR_cert_number', 80)->nullable();
            $table->date('EMR_cert_issuance')->nullable();
            $table->string('EMR_ID', 20)->nullable()->comment('ELECTRONIC MEDICAL RECORD PROVIDER ID');
            $table->string('HOSP_NAME', 50)->nullable()->comment('HOSPITAL NAME');
            $table->string('HOSP_ADDBRGY', 3)->nullable()->comment('BARANGAY ADDRESS OF FACILITY');
            $table->string('HOSP_ADDMUN', 2)->nullable()->comment('CITY OR MUNICIPALITY ADDRESS OF FACILITY');
            $table->string('HOSP_ADDPROV', 2)->nullable()->comment('PROVINCE ADDRESS OF FACILITY');
            $table->string('HOSP_ADDREG', 2)->nullable()->comment('REGION ADDRESS OF FACILITY');
            $table->string('HOSP_ADDZIPCODE', 4)->nullable()->comment('ZIP CODE ADDRESS OF FACILITY');
            $table->string('HOSP_ADDLHIO', 2)->nullable()->comment('LHIO CODE ADDRESS OF FACILITY');
            $table->string('HOSP_CLASS', 50)->nullable()->comment('HOSPITAL CLASSIFICATION');
            $table->string('url_admin', 200)->nullable();
            $table->string('url_secretary', 200)->nullable();
            $table->string('url_doctor', 200)->nullable();
            $table->string('url_port', 30)->nullable();
            $table->string('database_name', 200)->nullable();
            $table->longText('database_pw_base')->nullable();
            $table->enum('ownership_type', ['SOLE PROPRIETOR', 'PARTNERSHIP', 'SOLO CORPORATION', 'CORPORATION', 'LGU', 'COOPERATIVE'])->nullable();
            $table->string('businessgroup_name', 80)->nullable()->comment('MMGH, Healthway');
            $table->string('SECTOR', 1)->nullable()->comment('SECTOR OF FACILITY');
            $table->string('EMAIL_ADD', 50)->nullable()->comment('EMAIL ADDRESS OF THE FACILITY');
            $table->string('TIN', 15)->nullable()->comment('TIN NUMBER OF THE FACILITY');
            $table->string('TEL_NO', 20)->nullable()->comment('TELEPHONE NUMBER OF THE FACILITY');
            $table->string('TELEFAX', 20)->nullable()->comment('TELEFAX OF THE FACILITY');
            $table->date('DATE_REGISTERED')->comment('DATE REGISTERED');
            $table->string('tokenreceived', 200)->nullable();
            $table->dateTime('tokendatetime')->nullable();
            $table->string('cipher_key', 50)->nullable()->comment('PASSPHRASE/CIPHER KEY TO BE USED IN GENERATION OF REPORTS');
            $table->tinyInteger('enable_yakap')->nullable();
            $table->tinyInteger('enable_consultation')->nullable();
            $table->tinyInteger('enable_secretary')->nullable();
            $table->tinyInteger('enable_que')->nullable();
            $table->tinyInteger('enable_cashier')->nullable();
            $table->tinyInteger('enable_pharmacy')->nullable();
            $table->tinyInteger('enable_laboratory')->nullable();
            $table->tinyInteger('enable_radiology')->nullable();
            $table->string('admin_name', 80)->nullable();
            $table->string('corp_secretary', 80)->nullable();
            $table->string('phic_incharge', 80)->nullable();
            $table->string('accountant', 80)->nullable();
            $table->string('patient_pix_directory', 200)->nullable();
            $table->string('attachments_directory', 200)->nullable();
            $table->double('attachments_max_size_mb')->nullable();
            $table->double('yakap_max_amount')->nullable();
            $table->double('yakap_max_copay')->nullable();
            $table->string('smtp', 80)->nullable();
            $table->string('smtp_port', 5)->nullable();
            $table->string('smtp_emailadd', 80)->nullable();
            $table->string('email_pk', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kayakapmd_profile');
    }
};
