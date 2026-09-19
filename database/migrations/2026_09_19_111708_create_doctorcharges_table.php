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
        Schema::create('doctorcharges', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('docrefno', 100)->nullable();
            $table->dateTime('transdate')->nullable();
            $table->string('docname', 100)->nullable();
            $table->string('servicerefno', 100)->nullable();
            $table->string('servicename', 100)->nullable();
            $table->integer('quantity')->nullable();
            $table->float('total')->nullable();
            $table->float('discount')->nullable();
            $table->float('net_total')->nullable();
            $table->string('paymentrefno', 100)->nullable();
            $table->string('consultationrefno', 100)->nullable();
            $table->string('charge', 100)->nullable();
            $table->string('paymentmethod', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctorcharges');
    }
};
