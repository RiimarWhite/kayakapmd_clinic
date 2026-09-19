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
        Schema::create('doctorservices', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('servicerefno', 100)->nullable();
            $table->string('servicename', 100)->nullable();
            $table->string('servicedscr', 100)->nullable();
            $table->double('servicecharge')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('docrefno', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctorservices');
    }
};
