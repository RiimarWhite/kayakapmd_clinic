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
        Schema::create('servicesgroups', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('servicegroup_refno', 100)->nullable();
            $table->string('servicegroup_name', 100)->nullable();
            $table->string('servicegroup_dscr', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicesgroups');
    }
};
