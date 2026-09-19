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
            $table->unsignedBigInteger('id')->nullable();
            $table->string('chargerefno')->nullable();
            $table->string('charge_name')->nullable();
            $table->string('charge_category')->nullable();
            $table->double('charge_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charges_masterlist');
    }
};
