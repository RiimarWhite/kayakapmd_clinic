<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Detailed Comment: Migration to create stocks_groupings table for category-specific
 * grouping management (such as Imaging and Drugs & Meds groups), and add philhealth_gamot_essential
 * and dosage_form columns to stocks_listing with idempotent schema checks.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Detailed Comment: Create stocks_groupings table if not exists
        if (!Schema::hasTable('stocks_groupings')) {
            Schema::create('stocks_groupings', function (Blueprint $table) {
                $table->id();
                $table->string('group_code', 50)->nullable()->index();
                $table->string('category', 50)->index();
                $table->string('group_name', 100);
                $table->text('description')->nullable();
                $table->string('status', 20)->default('ACTIVE');
                $table->string('created_by', 50)->nullable();
                $table->timestamps();
            });

            // Detailed Comment: Pre-seed required default Imaging and Drugs & Meds groups
            $defaultGroupings = [
                ['group_code' => 'GRPIMG001', 'category' => 'IMAGING', 'group_name' => 'xray', 'description' => 'X-Ray Imaging', 'status' => 'ACTIVE', 'created_by' => 'system', 'created_at' => now(), 'updated_at' => now()],
                ['group_code' => 'GRPIMG002', 'category' => 'IMAGING', 'group_name' => 'mri', 'description' => 'Magnetic Resonance Imaging', 'status' => 'ACTIVE', 'created_by' => 'system', 'created_at' => now(), 'updated_at' => now()],
                ['group_code' => 'GRPIMG003', 'category' => 'IMAGING', 'group_name' => 'ct scan', 'description' => 'Computed Tomography Scan', 'status' => 'ACTIVE', 'created_by' => 'system', 'created_at' => now(), 'updated_at' => now()],
                ['group_code' => 'GRPIMG004', 'category' => 'IMAGING', 'group_name' => 'ultrasound', 'description' => 'Ultrasound Sonography', 'status' => 'ACTIVE', 'created_by' => 'system', 'created_at' => now(), 'updated_at' => now()],
                ['group_code' => 'GRPIMG005', 'category' => 'IMAGING', 'group_name' => 'ob ultrasound', 'description' => 'Obstetrical Ultrasound', 'status' => 'ACTIVE', 'created_by' => 'system', 'created_at' => now(), 'updated_at' => now()],
                ['group_code' => 'GRPIMG006', 'category' => 'IMAGING', 'group_name' => '2d echo', 'description' => '2D Echocardiogram', 'status' => 'ACTIVE', 'created_by' => 'system', 'created_at' => now(), 'updated_at' => now()],
                ['group_code' => 'GRPDRUG001', 'category' => 'DRUGS AND MEDS', 'group_name' => 'DRUGS AND MEDS', 'description' => 'Prescription and OTC Medications', 'status' => 'ACTIVE', 'created_by' => 'system', 'created_at' => now(), 'updated_at' => now()],
                ['group_code' => 'GRPDRUG002', 'category' => 'DRUGS AND MEDS', 'group_name' => 'MEDICAL SUPPLIES', 'description' => 'Clinical & Medical Consumables', 'status' => 'ACTIVE', 'created_by' => 'system', 'created_at' => now(), 'updated_at' => now()],
            ];

            DB::table('stocks_groupings')->insert($defaultGroupings);
        } else {
            // Detailed Comment: Add missing columns if stocks_groupings already existed
            Schema::table('stocks_groupings', function (Blueprint $table) {
                if (!Schema::hasColumn('stocks_groupings', 'group_code')) {
                    $table->string('group_code', 50)->nullable()->index()->after('id');
                }
                if (!Schema::hasColumn('stocks_groupings', 'status')) {
                    $table->string('status', 20)->default('ACTIVE')->after('description');
                }
                if (!Schema::hasColumn('stocks_groupings', 'created_by')) {
                    $table->string('created_by', 50)->nullable()->after('status');
                }
            });
        }

        // Detailed Comment: Add philhealth_gamot_essential and dosage_form to stocks_listing
        if (Schema::hasTable('stocks_listing')) {
            Schema::table('stocks_listing', function (Blueprint $table) {
                if (!Schema::hasColumn('stocks_listing', 'philhealth_gamot_essential')) {
                    $table->tinyInteger('philhealth_gamot_essential')->default(0)->after('yakap_essential');
                }
                if (!Schema::hasColumn('stocks_listing', 'dosage_form')) {
                    $table->string('dosage_form', 50)->default('N/A')->after('drug_dosage');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stocks_listing')) {
            Schema::table('stocks_listing', function (Blueprint $table) {
                if (Schema::hasColumn('stocks_listing', 'dosage_form')) {
                    $table->dropColumn('dosage_form');
                }
                if (Schema::hasColumn('stocks_listing', 'philhealth_gamot_essential')) {
                    $table->dropColumn('philhealth_gamot_essential');
                }
            });
        }

        Schema::dropIfExists('stocks_groupings');
    }
};
