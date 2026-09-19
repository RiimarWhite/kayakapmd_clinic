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
        Schema::create('lib_municipality', function (Blueprint $table) {
            $table->string('PROCODE', 2)->nullable();
            $table->string('PROVINCE', 2)->nullable();
            $table->string('MUNICIPALITY', 2)->nullable();
            $table->string('MUN_NAME', 60)->nullable();
            $table->string('LHIO', 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lib_municipality');
    }
};
