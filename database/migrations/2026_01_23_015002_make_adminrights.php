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
        // Detailed Comment: Check if 'adminrights' table already exists before attempting creation.
        // This ensures the migration safely passes if the table was pre-created via an external schema import.
        if (!Schema::hasTable('adminrights')) {
            Schema::create('adminrights', function (Blueprint $table) {
                $table->id();
                $table->string('adminrefno');
                $table->string('username');
                $table->string('password');
                $table->datetimes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('adminrights');
    }
};
