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
        Schema::create('charges_masterlist', function (Blueprint $table) {
            $table->id();
            $table->string('chargerefno')->unique();
            $table->string('charge_name');
            $table->string('charge_category');
            $table->float('charge_amount');
        });

        Schema::create('pxcharges', function (Blueprint $table) {
            $table->id();
            $table->string('chargerefno');
            $table->string('consultationrefno');
            $table->string('docrefno');
            $table->string('transactedby');
            $table->dateTime('transacteddate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charges');
        Schema::dropIfExists('pxcharges');
    }
};
