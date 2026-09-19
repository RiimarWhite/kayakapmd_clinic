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
        Schema::create('hmo_masterlist', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('hmocode', 50)->nullable();
            $table->string('hmoname', 120)->nullable();
            $table->string('hmoaddress', 200)->nullable();
            $table->string('coacode', 30)->nullable();
            $table->string('accre_no', 80)->nullable();
            $table->enum('hmotype', ['HMO', 'COMPANY', 'GOVERNMENT'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hmo_masterlist');
    }
};
