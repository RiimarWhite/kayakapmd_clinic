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
        Schema::create('token_info', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->string('clientcode', 50)->nullable();
            $table->string('userid', 50)->nullable();
            $table->string('username', 80)->nullable();
            $table->dateTime('initiated')->nullable();
            $table->tinyInteger('status')->nullable()->comment('1 success 0 unsuccessful');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_info');
    }
};
