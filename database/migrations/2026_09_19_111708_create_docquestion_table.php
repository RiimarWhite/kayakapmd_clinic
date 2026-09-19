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
        Schema::create('docquestion', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('docquestionrefno', 60)->nullable();
            $table->string('secrefno', 60)->nullable();
            $table->longText('question')->nullable();
            $table->string('docrefno', 50)->nullable();
            $table->string('docname', 100)->nullable();
            $table->dateTime('recordeddate')->nullable();
            $table->string('recordedby', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docquestion');
    }
};
