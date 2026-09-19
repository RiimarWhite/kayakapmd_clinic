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
        Schema::create('lib_hci_class', function (Blueprint $table) {
            $table->string('CLASS_CODE', 2)->nullable()->comment('CLASSIFICATION CODE');
            $table->string('CLASS_DEF', 100)->nullable()->comment('CLASSIFICATION CODE DEFINITION');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lib_hci_class');
    }
};
