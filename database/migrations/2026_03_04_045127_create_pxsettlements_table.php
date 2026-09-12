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
        Schema::create('pxsettlements', function (Blueprint $table) {
            $table->id();
            $table->string('transactionrefno')->unique();
            $table->string('consultationrefno');
            $table->float('net_total');
            $table->float('cash')->nullable();
            $table->float('cta')->nullable();
            $table->string('cta_type')->nullable();
            $table->float('something')->nullable();
            $table->float('hmo')->nullable();
            $table->string('hmo_type')->nullable();
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pxsettlements');
    }
};
