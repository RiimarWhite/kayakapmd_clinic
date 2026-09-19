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
        Schema::create('dw_lib_meds_generic', function (Blueprint $table) {
            $table->string('gen_code', 10)->nullable();
            $table->string('gen_desc', 100)->nullable();
            $table->dateTime('updated')->nullable()->comment('DATE WHEN THE RECORD WAS ADDED');
            $table->string('updatedby', 80)->nullable()->comment('USER WHO ADDED THE RECORD');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dw_lib_meds_generic');
    }
};
