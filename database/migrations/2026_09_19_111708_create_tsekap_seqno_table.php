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
        Schema::create('tsekap_seqno', function (Blueprint $table) {
            $table->string('SEQ_NAME', 20)->nullable()->comment('SEQUENCE NAME');
            $table->string('SEQ_FORMAT', 15)->nullable()->comment('SEQUENCE FORMAT');
            $table->string('SEQ_DESC', 100)->nullable()->comment('SEQUENCE DESCRIPTION');
            $table->string('SEQ_PREFIX', 10)->nullable()->comment('SEQUENCE PREFIX');
            $table->string('CYCLE_PERIOD_FORMAT', 15)->nullable()->comment('CYCLE PERIOD FORMAT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tsekap_seqno');
    }
};
