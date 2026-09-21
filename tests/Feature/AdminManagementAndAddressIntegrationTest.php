<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ChargesCategoryModel;
use App\Models\ConsultationModel;
use App\Models\DiagnosticsCategoryModel;
use App\Models\HMOModel;
use App\Models\KayakapProfileModel;
use App\Models\PxChargesModel;
use App\Models\SettlementsModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Detailed Comment: Feature test suite covering:
 * 1. PSGC Address Cascading API endpoints (Regions, Provinces, Municipalities, Barangays, Zipcodes)
 * 2. Hospital/Company Profile update with PSGC geographic codes
 * 3. HMO Masterlist CRUD (fetch, add, edit, delete)
 * 4. Diagnostic Categories CRUD (fetch, create, edit, delete)
 * 5. Charge Categories CRUD (fetch, create, edit, delete)
 * 6. Consultations Billing CRUD (active consultations, fetch, add, edit, delete)
 * 7. Consultations Settlements CRUD (fetch, add, edit, delete)
 */
class AdminManagementAndAddressIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Test PSGC address cascading endpoints across Region -> Province -> Mun -> Brgy -> Zipcode.
     */
    public function test_psgc_address_cascading_endpoints(): void
    {
        $admin = AdminModel::first();

        // Seed sample PSGC records into lib_ tables
        DB::table('lib_region')->insert([
            'REGION_CODE' => '04',
            'PRO_CODE' => '04',
            'REGION_NAME' => 'REGION IV-A (CALABARZON)',
            'REGION_DESC' => 'REGION IV-A (CALABARZON)'
        ]);

        DB::table('lib_province')->insert([
            'PROCODE' => '04',
            'PROVINCE' => 'CAVITE',
            'PROV_NAME' => 'CAVITE'
        ]);

        DB::table('lib_municipality')->insert([
            'PROCODE' => '04',
            'PROVINCE' => 'CAVITE',
            'MUNICIPALITY' => 'IMUS',
            'MUN_NAME' => 'CITY OF IMUS'
        ]);

        DB::table('lib_barangay')->insert([
            'PROCODE' => '04',
            'PROVINCE' => 'CAVITE',
            'MUNICIPALITY' => 'IMUS',
            'BARANGAY' => 'POBLACION_I_A',
            'BRGY_NAME' => 'POBLACION I-A'
        ]);

        DB::table('lib_zipcode')->insert([
            'PROCODE' => '04',
            'PROVINCE' => 'CAVITE',
            'MUNICIPALITY' => 'IMUS',
            'ZIP_CODE' => '4103'
        ]);

        // 1. Test Regions
        $resRegions = $this->actingAs($admin, 'admin')->getJson('/api/address/regions');
        $resRegions->assertOk()->assertJsonStructure(['regions']);
        $this->assertNotEmpty($resRegions->json('regions'));

        // 2. Test Provinces
        $resProvinces = $this->actingAs($admin, 'admin')->getJson('/api/address/provinces?pro_code=04');
        $resProvinces->assertOk()->assertJsonStructure(['provinces']);
        $this->assertEquals('CAVITE', $resProvinces->json('provinces.0.PROVINCE'));

        // 3. Test Municipalities
        $resMun = $this->actingAs($admin, 'admin')->getJson('/api/address/municipalities?pro_code=04&province=CAVITE');
        $resMun->assertOk()->assertJsonStructure(['municipalities']);
        $this->assertEquals('IMUS', $resMun->json('municipalities.0.MUNICIPALITY'));

        // 4. Test Barangays
        $resBrgy = $this->actingAs($admin, 'admin')->getJson('/api/address/barangays?pro_code=04&province=CAVITE&municipality=IMUS');
        $resBrgy->assertOk()->assertJsonStructure(['barangays']);
        $this->assertEquals('POBLACION_I_A', $resBrgy->json('barangays.0.BARANGAY'));

        // 5. Test Zipcode
        $resZip = $this->actingAs($admin, 'admin')->getJson('/api/address/zipcode?pro_code=04&province=CAVITE&municipality=IMUS');
        $resZip->assertOk()->assertJson(['zipcode' => '4103']);
    }

    /**
     * Detailed Comment: Test company profile load and update with PSGC address attributes.
     */
    public function test_company_profile_load_and_update_with_address(): void
    {
        $admin = AdminModel::first();

        // Ensure kayakapmd_profile record exists
        if (!KayakapProfileModel::first()) {
            KayakapProfileModel::create([
                'HOSP_NAME' => 'Kayakap Medical Clinic',
                'TEL_NO' => '09123456789',
                'EMAIL_ADD' => 'info@kayakapmd.com',
                'DATE_REGISTERED' => now()
            ]);
        }

        // Test Load Profile
        $loadRes = $this->actingAs($admin, 'admin')->postJson('/api/load_company_profile');
        $loadRes->assertOk()->assertJson(['success' => true]);

        // Test Update Profile with address fields
        $updateRes = $this->actingAs($admin, 'admin')->postJson('/api/update_profile', [
            'comp_name' => 'Kayakap Diagnostic & Medical Center',
            'comp_tel' => '0281234567',
            'comp_email' => 'admin@kayakapmd.com',
            'phregion' => '04',
            'phprov' => 'CAVITE',
            'phmun' => 'IMUS',
            'phbrgy' => 'POBLACION_I_A',
            'phzipcode' => '4103',
        ]);
        $updateRes->assertOk()->assertJson(['success' => true]);

        $profile = KayakapProfileModel::first();
        $this->assertEquals('Kayakap Diagnostic & Medical Center', $profile->HOSP_NAME);
        $this->assertEquals('04', $profile->HOSP_ADDREG);
        $this->assertEquals('CAVITE', $profile->HOSP_ADDPROV);
        $this->assertEquals('IMUS', $profile->HOSP_ADDMUN);
        $this->assertEquals('POBLACION_I_A', $profile->HOSP_ADDBRGY);
        $this->assertEquals('4103', $profile->HOSP_ADDZIPCODE);
    }

    /**
     * Detailed Comment: Test HMO Masterlist CRUD operations.
     */
    public function test_hmo_masterlist_crud(): void
    {
        $admin = AdminModel::first();

        // 1. Add HMO
        $addRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/add_hmo', [
            'hmocode' => 'INTELLICARE',
            'hmoname' => 'Asalus Corporation (Intellicare)',
            'hmotype' => 'HMO',
            'hmoaddress' => 'Makati City',
            'accre_no' => 'ACCRE-999',
            'coacode' => 'COA-101'
        ]);
        $addRes->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('hmo_masterlist', ['hmocode' => 'INTELLICARE']);

        // 2. Fetch HMO
        $fetchRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/fetch_hmo');
        $fetchRes->assertOk()->assertJson(['success' => true]);
        $this->assertNotEmpty($fetchRes->json('hmos'));

        // 3. Edit HMO
        $editRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/edit_hmo', [
            'code' => 'INTELLICARE',
            'hmocode' => 'INTELLICARE_V2',
            'hmoname' => 'Intellicare Healthcare Inc.',
            'hmotype' => 'HMO',
            'hmoaddress' => 'BGC, Taguig City',
            'accre_no' => 'ACCRE-1000',
            'coacode' => 'COA-102'
        ]);
        $editRes->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('hmo_masterlist', ['hmocode' => 'INTELLICARE_V2', 'hmoname' => 'Intellicare Healthcare Inc.']);

        // 4. Delete HMO
        $delRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/delete_hmo', [
            'code' => 'INTELLICARE_V2'
        ]);
        $delRes->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('hmo_masterlist', ['hmocode' => 'INTELLICARE_V2']);
    }

    /**
     * Detailed Comment: Test Diagnostic Category Edit and CRUD.
     */
    public function test_diagnostic_category_crud(): void
    {
        $admin = AdminModel::first();

        // 1. Create Diagnostic Category
        $cat = DiagnosticsCategoryModel::create([
            'category_refno' => 'DIAG_CAT_001',
            'category_name' => 'Initial Diagnostic Category'
        ]);

        // 2. Fetch Diagnostic Categories
        $fetchRes = $this->actingAs($admin, 'admin')->postJson('/api/fetch_diagnostic_category');
        $fetchRes->assertOk()->assertJsonStructure(['categories']);

        // 3. Edit Diagnostic Category
        $editRes = $this->actingAs($admin, 'admin')->postJson('/api/edit_diagnostic_category', [
            'category_refno' => $cat->category_refno,
            'category_name' => 'Updated Diagnostic Category'
        ]);
        $editRes->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('diagnostic_category', [
            'category_refno' => $cat->category_refno,
            'category_name' => 'Updated Diagnostic Category'
        ]);

        // 4. Delete Diagnostic Category
        $delRes = $this->actingAs($admin, 'admin')->postJson('/api/delete_diagnostic_category', [
            'category_refno' => $cat->category_refno
        ]);
        $delRes->assertOk();
    }

    /**
     * Detailed Comment: Test Charge Category CRUD endpoints.
     */
    public function test_charge_category_crud(): void
    {
        $admin = AdminModel::first();

        // 1. Create Charge Category
        $createRes = $this->actingAs($admin, 'admin')->postJson('/api/create_charge_category', [
            'categoryname' => 'Dental Care'
        ]);
        $createRes->assertOk();
        $this->assertDatabaseHas('charges_category', ['categoryname' => 'Dental Care']);

        $cat = ChargesCategoryModel::where('categoryname', 'Dental Care')->first();

        // 2. Fetch Charge Categories
        $fetchRes = $this->actingAs($admin, 'admin')->postJson('/api/fetch_charge_categories');
        $fetchRes->assertOk()->assertJsonStructure(['categories']);

        // 3. Edit Charge Category
        $editRes = $this->actingAs($admin, 'admin')->postJson('/api/edit_charge_category', [
            'ecategoryrefno' => $cat->categoryrefno,
            'categoryname' => 'Advanced Dental Care'
        ]);
        $editRes->assertOk();
        $this->assertDatabaseHas('charges_category', ['categoryname' => 'Advanced Dental Care']);

        // 4. Delete Charge Category
        $delRes = $this->actingAs($admin, 'admin')->postJson('/api/delete_charge_category', [
            'categoryrefno' => $cat->categoryrefno
        ]);
        $delRes->assertOk();
        $this->assertDatabaseMissing('charges_category', ['categoryrefno' => $cat->categoryrefno]);
    }

    /**
     * Detailed Comment: Test Consultations Billing CRUD endpoints.
     */
    public function test_consultations_billing_crud(): void
    {
        $admin = AdminModel::first();

        // Create consultation record for linking
        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CON_BILL_TEST_001',
            'caseno' => 'CASE_BILL_001',
            'pincode' => 'PIN_BILL_001',
            'patientname' => 'DOE, JANE',
            'finadiagnosis' => 'Mild Rhinitis',
            'status' => 'IN_CONSULTATION',
            'consultation_date' => now()
        ]);

        // 1. Active Consultations
        $activeRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/active_consultations');
        $activeRes->assertOk()->assertJson(['success' => true]);

        // 2. Add Billing Charge
        $addRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/add_billing', [
            'consultationrefno' => 'CON_BILL_TEST_001',
            'pxname' => 'DOE, JANE',
            'servicename' => 'Complete Blood Count',
            'group_category' => 'Laboratory',
            'payment_type' => 'CASH',
            'quantity' => 2,
            'retail' => 250.00,
            'total' => 500.00,
            'discount' => 50.00,
            'net_total' => 450.00
        ]);
        $addRes->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('pxcharges', [
            'consultationrefno' => 'CON_BILL_TEST_001',
            'servicename' => 'Complete Blood Count',
            'quantity' => 2
        ]);

        $charge = PxChargesModel::where('consultationrefno', 'CON_BILL_TEST_001')->first();

        // 3. Fetch Billings
        $fetchRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/fetch_billings');
        $fetchRes->assertOk()->assertJson(['success' => true]);
        $this->assertNotEmpty($fetchRes->json('data'));

        // 4. Edit Billing Charge
        $editRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/edit_billing', [
            'id' => $charge->id,
            'consultationrefno' => 'CON_BILL_TEST_001',
            'pxname' => 'DOE, JANE',
            'servicename' => 'Complete Blood Count with Platelet',
            'group_category' => 'Laboratory',
            'payment_type' => 'HMO',
            'quantity' => 1,
            'retail' => 350.00,
            'total' => 350.00,
            'discount' => 0.00,
            'net_total' => 350.00
        ]);
        $editRes->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('pxcharges', [
            'id' => $charge->id,
            'servicename' => 'Complete Blood Count with Platelet',
            'payment_type' => 'HMO'
        ]);

        // 5. Delete Billing Charge
        $delRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/delete_billing', [
            'id' => $charge->id
        ]);
        $delRes->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('pxcharges', ['id' => $charge->id]);
    }

    /**
     * Detailed Comment: Test Consultations Settlements CRUD endpoints.
     */
    public function test_consultations_settlements_crud(): void
    {
        $admin = AdminModel::first();

        // 1. Add Settlement
        $addRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/add_settlement', [
            'consultationrefno' => 'CON_STL_TEST_001',
            'docname' => 'Gomez, Mario',
            'total_doctorspf' => 800.00,
            'total_meds' => 200.00,
            'total_lab' => 500.00,
            'total_others' => 0.00,
            'total_gross' => 1500.00,
            'less_vat' => 0.00,
            'less_discount' => 100.00,
            'less_hmo' => 900.00,
            'less_phic' => 0.00,
            'net_payable' => 500.00,
            'payment_cash' => 500.00,
            'payment_card' => 0.00
        ]);
        $addRes->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('pxsettlements', [
            'consultationrefno' => 'CON_STL_TEST_001',
            'payment_cash' => 500.00
        ]);

        // 2. Fetch Settlements
        $fetchRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/fetch_settlements');
        $fetchRes->assertOk()->assertJson(['success' => true]);
        $this->assertNotEmpty($fetchRes->json('data'));

        // 3. Edit Settlement
        $editRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/edit_settlement', [
            'consultationrefno' => 'CON_STL_TEST_001',
            'docname' => 'Gomez, Mario MD',
            'total_doctorspf' => 800.00,
            'total_meds' => 200.00,
            'total_lab' => 500.00,
            'total_others' => 100.00,
            'total_gross' => 1600.00,
            'less_vat' => 0.00,
            'less_discount' => 100.00,
            'less_hmo' => 1000.00,
            'less_phic' => 0.00,
            'net_payable' => 500.00,
            'payment_cash' => 0.00,
            'payment_card' => 500.00,
            'cta_type' => 'VISA'
        ]);
        $editRes->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('pxsettlements', [
            'consultationrefno' => 'CON_STL_TEST_001',
            'payment_card' => 500.00,
            'cta_type' => 'VISA'
        ]);

        // 4. Delete Settlement
        $delRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/delete_settlement', [
            'consultationrefno' => 'CON_STL_TEST_001'
        ]);
        $delRes->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('pxsettlements', ['consultationrefno' => 'CON_STL_TEST_001']);
    }

    /**
     * Detailed Comment: Test DataTables server-side pagination, directional ordering, global search, and column-specific filtering.
     */
    public function test_serverside_datatables_filtering_and_sorting(): void
    {
        $admin = AdminModel::first();

        // 1. Seed HMO records
        HMOModel::create(['hmocode' => 'MAXI_01', 'hmoname' => 'Maxicare Healthcare', 'hmotype' => 'HMO']);
        HMOModel::create(['hmocode' => 'PHILHEALTH_01', 'hmoname' => 'PhilHealth Insurance', 'hmotype' => 'GOVERNMENT']);
        HMOModel::create(['hmocode' => 'MEDICARD_01', 'hmoname' => 'Medicard Philippines', 'hmotype' => 'HMO']);

        // Test HMO server-side global search + sort
        $hmoRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/fetch_hmo', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'Medicard'],
            'order' => [['column' => 2, 'dir' => 'asc']]
        ]);
        $hmoRes->assertOk()->assertJson(['draw' => 1]);
        $this->assertEquals(1, $hmoRes->json('recordsFiltered'));
        $this->assertEquals('MEDICARD_01', $hmoRes->json('data.0.hmocode'));

        // Test HMO column filter by type (picklist regex)
        $hmoTypeRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/fetch_hmo', [
            'draw' => 2,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => null],
                1 => ['data' => 'hmocode', 'search' => ['value' => '']],
                2 => ['data' => 'hmoname', 'search' => ['value' => '']],
                3 => ['data' => 'hmotype', 'search' => ['value' => '^(GOVERNMENT)$']],
            ]
        ]);
        $hmoTypeRes->assertOk();
        $this->assertEquals('GOVERNMENT', $hmoTypeRes->json('data.0.hmotype'));

        // Test HMO free-text filter on type
        $hmoFreeTextRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/fetch_hmo', [
            'draw' => 3,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => null],
                1 => ['data' => 'hmocode', 'search' => ['value' => '']],
                2 => ['data' => 'hmoname', 'search' => ['value' => '']],
                3 => ['data' => 'hmotype', 'search' => ['value' => 'GOV']],
            ]
        ]);
        $hmoFreeTextRes->assertOk();
        $this->assertEquals(1, $hmoFreeTextRes->json('recordsFiltered'));
        $this->assertEquals('GOVERNMENT', $hmoFreeTextRes->json('data.0.hmotype'));

        // 2. Test Charge Categories server-side
        ChargesCategoryModel::create(['categoryrefno' => 'CHG_CAT_100', 'categoryname' => 'Consultation Fees']);
        ChargesCategoryModel::create(['categoryrefno' => 'CHG_CAT_101', 'categoryname' => 'Laboratory Tests']);

        $chargeCatRes = $this->actingAs($admin, 'admin')->postJson('/api/fetch_charge_categories', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => null],
                1 => ['data' => 'categoryrefno', 'search' => ['value' => '']],
                2 => ['data' => 'categoryname', 'search' => ['value' => 'Laboratory']]
            ],
            'order' => [['column' => 2, 'dir' => 'asc']]
        ]);
        $chargeCatRes->assertOk()->assertJson(['draw' => 1]);
        $this->assertEquals(1, $chargeCatRes->json('recordsFiltered'));
        $this->assertEquals('Laboratory Tests', $chargeCatRes->json('data.0.categoryname'));

        // 3. Test Diagnostic Categories server-side
        DiagnosticsCategoryModel::create(['category_refno' => 'DIA_CAT_100', 'category_name' => 'Hematology']);
        DiagnosticsCategoryModel::create(['category_refno' => 'DIA_CAT_101', 'category_name' => 'Radiology']);

        $diagCatRes = $this->actingAs($admin, 'admin')->postJson('/api/fetch_diagnostic_category', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'Hematology'],
            'order' => [['column' => 2, 'dir' => 'asc']]
        ]);
        $diagCatRes->assertOk()->assertJson(['draw' => 1]);
        $this->assertEquals(1, $diagCatRes->json('recordsFiltered'));
        $this->assertEquals('Hematology', $diagCatRes->json('data.0.category_name'));

        // 4. Test Billing server-side with transdate and column ordering
        PxChargesModel::create([
            'id' => 8881,
            'transdate' => '2026-09-20',
            'consultationrefno' => 'CON_SS_BILL_01',
            'pxname' => 'Cruz, Juan',
            'servicename' => 'CBC Routine',
            'group_category' => 'Laboratory',
            'payment_type' => 'POCKET',
            'total' => 200.00,
            'discount' => 0.00,
            'net_total' => 200.00
        ]);

        $billRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/fetch_billings', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => null],
                1 => ['data' => 'transdate', 'search' => ['value' => '2026-09']],
                2 => ['data' => 'consultationrefno', 'search' => ['value' => 'CON_SS_BILL_01']],
            ],
            'order' => [['column' => 1, 'dir' => 'desc']]
        ]);
        $billRes->assertOk()->assertJson(['draw' => 1]);
        $this->assertEquals(1, $billRes->json('recordsFiltered'));
        $this->assertEquals('CBC Routine', $billRes->json('data.0.servicename'));

        // 5. Test Settlements server-side with real DB columns (payment_cash, less_hmo, net_payable)
        SettlementsModel::create([
            'consultationrefno' => 'CON_SS_STL_01',
            'docname' => 'Santos, Clara',
            'total_gross' => 1000.00,
            'payment_cash' => 500.00,
            'payment_card' => 0.00,
            'less_hmo' => 500.00,
            'less_phic' => 0.00,
            'net_payable' => 1000.00,
            'created' => '2026-09-20 10:00:00'
        ]);

        $stlRes = $this->actingAs($admin, 'admin')->postJson('/api/admin/fetch_settlements', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => null],
                1 => ['data' => 'created', 'search' => ['value' => '']],
                2 => ['data' => 'consultationrefno', 'search' => ['value' => 'CON_SS_STL_01']],
                3 => ['data' => 'docname', 'search' => ['value' => '']],
                4 => ['data' => 'total_gross', 'search' => ['value' => '']],
                5 => ['data' => 'payment_cash', 'search' => ['value' => '500']],
                6 => ['data' => 'payment_card', 'search' => ['value' => '']],
                7 => ['data' => 'less_hmo', 'search' => ['value' => '500']],
            ],
            'order' => [['column' => 5, 'dir' => 'desc']]
        ]);
        $stlRes->assertOk()->assertJson(['draw' => 1]);
        $this->assertEquals(1, $stlRes->json('recordsFiltered'));
        $this->assertEquals('CON_SS_STL_01', $stlRes->json('data.0.consultationrefno'));

        // 6. Test Users Masterlist (/api/fetch_secretaries) server-side
        $secRes = $this->actingAs($admin, 'admin')->postJson('/api/fetch_secretaries', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                0 => ['data' => null],
                1 => ['data' => 'account_type', 'search' => ['value' => '^(Admin)$']],
                2 => ['data' => 'source_table', 'search' => ['value' => '']],
                3 => ['data' => 'username', 'search' => ['value' => '']],
                4 => ['data' => 'fullname', 'search' => ['value' => '']],
                5 => ['data' => 'seccontactno', 'search' => ['value' => '']],
                6 => ['data' => 'secemail', 'search' => ['value' => '']]
            ],
            'order' => [['column' => 3, 'dir' => 'asc']]
        ]);
        $secRes->assertOk()->assertJson(['draw' => 1]);
        $this->assertNotEmpty($secRes->json('data'));
        foreach ($secRes->json('data') as $row) {
            $this->assertEquals('Admin', $row['account_type']);
        }
    }
}
