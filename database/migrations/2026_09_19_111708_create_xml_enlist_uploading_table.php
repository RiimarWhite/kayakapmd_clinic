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
        Schema::create('xml_enlist_uploading', function (Blueprint $table) {
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('UPLOAD_ID', 27)->nullable();
            $table->longText('UPLOAD_XML')->comment('XML DATA');
            $table->dateTime('DATE_UPLOADED')->comment('MODULE WHERE XML UPLOADED');
            $table->string('RANGE_DATE', 50)->nullable()->comment('Range from start to end date of search results');
            $table->enum('status', ['PENDING', 'DONE'])->nullable();
            $table->string('importedby', 80)->nullable();
            $table->dateTime('imported')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xml_enlist_uploading');
    }
};
