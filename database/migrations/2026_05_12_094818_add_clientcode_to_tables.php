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
        Schema::table('secretaryrights', function (Blueprint $table) {
            $table->string('clientcode')->nullable()->index();
        });

        Schema::table('adminrights', function (Blueprint $table) {
            $table->string('clientcode')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('secretaryrights', function (Blueprint $table) {
            $table->dropColumn('secretaryrights');
        });

        Schema::table('adminrights', function (Blueprint $table) {
            $table->dropColumn('adminrighs');
        });
    }
};
