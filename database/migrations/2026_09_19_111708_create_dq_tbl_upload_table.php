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
        Schema::create('dq_tbl_upload', function (Blueprint $table) {
            $table->string('UPLOAD_ID', 27)->nullable();
            $table->longText('UPLOAD_XML')->comment('XML DATA');
            $table->string('UPLOAD_MODULE', 100)->nullable()->comment('MODULE WHERE XML UPLOADED');
            $table->dateTime('DATE_UPLOADED')->comment('MODULE WHERE XML UPLOADED');
            $table->string('RANGE_DATE', 50)->nullable()->comment('Range from start to end date of search results');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dq_tbl_upload');
    }
};
