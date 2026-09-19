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
        Schema::create('docrequests', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('consultationrefno')->nullable();
            $table->string('docrefno')->nullable();
            $table->string('requestrefno')->nullable();
            $table->string('request_dscr')->nullable();
            $table->string('request_catg')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docrequests');
    }
};
