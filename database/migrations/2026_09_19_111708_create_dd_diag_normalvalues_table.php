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
        Schema::create('dd_diag_normalvalues', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('dw_clientcode', 12)->nullable();
            $table->string('prodcode', 50)->nullable();
            $table->string('phic_reference_code', 50)->nullable();
            $table->string('testcode', 50)->nullable();
            $table->string('test_dscr', 120)->nullable()->comment('cbc createnin');
            $table->string('grouping', 120)->nullable();
            $table->string('sub_grouping', 120)->nullable();
            $table->enum('sex_applicable', ['BOTH', 'MALE', 'FEMALE'])->nullable();
            $table->double('age_min')->nullable();
            $table->double('age_max')->nullable();
            $table->tinyInteger('is_numeric_result')->nullable()->comment('1 = yes then can make min and max value else 0');
            $table->double('is_numeric_min_nv')->nullable();
            $table->double('is_numeric_max_nv')->nullable();
            $table->enum('nv_default', ['SI', 'CONVENTIONAL'])->nullable();
            $table->string('nv_si_normal', 200)->nullable();
            $table->string('nv_conventional_normal', 200)->nullable();
            $table->string('unit_dscr', 50)->nullable();
            $table->string('tablename', 100)->nullable()->comment('name of tablename in the database');
            $table->string('createdby', 80)->nullable();
            $table->dateTime('created')->nullable();
            $table->string('updatedby', 80)->nullable();
            $table->dateTime('updated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_diag_normalvalues');
    }
};
