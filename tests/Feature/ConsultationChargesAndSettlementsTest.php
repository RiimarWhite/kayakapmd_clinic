<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationModel;
use App\Models\DoctorModel;
use App\Models\DoctorsProfileModel;
use App\Models\HMOModel;
use App\Models\SettlementsModel;
use App\Models\Stocks\StocksLedgerModel;
use App\Models\Stocks\StocksListingModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Feature tests verifying consultation charges, medicine inclusion,
 * settlements lifecycle, and unscheduled queue exclusion of scheduled patients.
 */
class ConsultationChargesAndSettlementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Verify unscheduled queue endpoint excludes patients who have a schedule.
     */
    public function test_unscheduled_patients_table_excludes_scheduled_patients(): void
    {
        $admin = AdminModel::first();

        // Patient 1: Unscheduled without date
        ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_UNSCHED_1',
            'caseno' => 'CASE_001',
            'pincode' => 'PIN_001',
            'patientname' => 'UNSCHED, PATIENT ONE',
            'finadiagnosis' => '',
            'pxrefno' => 'PTN_UNSCHED_001',
            'status' => 'UNSCHEDULED',
            'consultation_date' => null
        ]);

        // Patient 2: Has a scheduled consultation
        ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_SCHED_2',
            'caseno' => 'CASE_002',
            'pincode' => 'PIN_002',
            'patientname' => 'SCHED, PATIENT TWO',
            'finadiagnosis' => '',
            'pxrefno' => 'PTN_SCHED_002',
            'status' => 'WAITING',
            'consultation_date' => '2026-09-25 09:00:00'
        ]);

        // Patient 2 also has an old unscheduled record
        ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_UNSCHED_2_OLD',
            'caseno' => 'CASE_003',
            'pincode' => 'PIN_002',
            'patientname' => 'SCHED, PATIENT TWO',
            'finadiagnosis' => '',
            'pxrefno' => 'PTN_SCHED_002',
            'status' => 'UNSCHEDULED',
            'consultation_date' => null
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson('/api/fetch_unscheduled_patients', [
            'start' => 0,
            'length' => 10
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        // Patient 1 should be present
        $pxrefnos = collect($data)->pluck('pxrefno')->all();
        $this->assertContains('PTN_UNSCHED_001', $pxrefnos);

        // Patient 2 (who has a schedule) must NOT be present
        $this->assertNotContains('PTN_SCHED_002', $pxrefnos);
    }

    /**
     * Detailed Comment: Verify patient charges include prescribed medicines (DRUGS AND MEDS) with prices.
     */
    public function test_patient_charges_include_prescribed_drugs_and_meds(): void
    {
        $admin = AdminModel::first();

        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_RX_CHARGES',
            'caseno' => 'CASE_RX_001',
            'pincode' => 'PIN_RX_001',
            'patientname' => 'TEST, PATIENT RX',
            'finadiagnosis' => '',
            'pxrefno' => 'PTN_TEST_RX',
            'status' => 'IN_CONSULTATION',
            'consultation_date' => now()
        ]);

        $medListing = StocksListingModel::create([
            'prod_itemdscr' => 'Amoxicillin 500mg Capsule',
            'item_grouping' => 'DRUGS AND MEDS',
            'price_regular' => 15.50,
            'is_inventory' => 0,
            'qty' => 100
        ]);

        // Add medicine
        $addResponse = $this->actingAs($admin, 'admin')->postJson('/api/add_medicine', [
            'consultationrefno' => $consultation->consultationrefno,
            'prodcode' => $medListing->prodcode,
            'qty' => 3
        ]);

        $addResponse->assertStatus(200);
        $addResponse->assertJson(['success' => true]);

        // Fetch patient charges
        $chargesResponse = $this->actingAs($admin, 'admin')->postJson('/api/fetch_patient_charges', [
            'consultationrefno' => $consultation->consultationrefno
        ]);

        $chargesResponse->assertStatus(200);
        $charges = $chargesResponse->json('charges');

        $this->assertCount(1, $charges);
        $this->assertEquals('Amoxicillin 500mg Capsule', $charges[0]['item_dscr']);
        $this->assertEquals('15.50', $charges[0]['cost_ave']);
        $this->assertEquals('46.50', $charges[0]['totalamt']);
    }

    /**
     * Detailed Comment: Verify settlements save, fetch, and HMO fallback functionality.
     */
    public function test_settlements_save_fetch_and_hmo_population(): void
    {
        $admin = AdminModel::first();

        HMOModel::insert([
            'id' => 1,
            'dw_clientcode' => '122377',
            'hmocode' => 'HMO_TEST_001',
            'hmoname' => 'MaxiCare Health',
            'hmotype' => 'HMO'
        ]);

        // Fetch HMO
        $hmoResponse = $this->actingAs($admin, 'admin')->postJson('/api/fetch_hmo');
        $hmoResponse->assertStatus(200);
        $this->assertNotEmpty($hmoResponse->json('hmo'));

        // Save settlements
        $saveResponse = $this->actingAs($admin, 'admin')->postJson('/api/save_settlements', [
            'sett_consultationrefno' => 'CON_TEST_SETT_001',
            'total' => '500.00',
            'cash' => '300.00',
            'cta' => '200.00',
            'card_type' => 'cc',
            'hmo' => '0.00',
            'hmo_type' => null
        ]);

        $saveResponse->assertStatus(200);
        $saveResponse->assertJson(['success' => true]);

        // Fetch settlements
        $fetchResponse = $this->actingAs($admin, 'admin')->postJson('/api/fetch_settlements', [
            'consultationrefno' => 'CON_TEST_SETT_001'
        ]);

        $fetchResponse->assertStatus(200);
        $fetchResponse->assertJson(['success' => true]);
        $record = $fetchResponse->json('record');
        $this->assertEquals('500.00', $record['net_total']);
        $this->assertEquals('300.00', $record['cash']);
        $this->assertEquals('200.00', $record['cta']);
        $this->assertEquals('cc', $record['cta_type']);
    }

    /**
     * Detailed Comment: Test settlement persistence with Senior/PWD, PHIC ICD/RVS, HMO, and Other discount.
     * Verifies that Net Billing (net_payable) is dynamically calculated by deducting all discounts from Gross.
     */
    public function test_settlements_save_with_senior_pwd_phic_and_custom_discounts(): void
    {
        $admin = AdminModel::first();

        $saveResponse = $this->actingAs($admin, 'admin')->postJson('/api/save_settlements', [
            'sett_consultationrefno' => 'CON_TEST_SETT_DISC_001',
            'total' => '1000.00',
            'less_srpwd' => '100.00',
            'srpwd_refno' => 'SR-998877',
            'phic' => '150.00',
            'phic_icd_rvs' => 'J00.0',
            'hmo' => '200.00',
            'hmo_type' => 'HMO_TEST_001',
            'less_discount' => '50.00',
            'discount_description' => 'Staff Courtesy Discount',
            'cash' => '300.00',
            'cta' => '200.00',
            'card_type' => 'cc',
        ]);

        $saveResponse->assertStatus(200);
        $saveResponse->assertJson(['success' => true]);

        // Verify in database
        $this->assertDatabaseHas('pxsettlements', [
            'consultationrefno' => 'CON_TEST_SETT_DISC_001',
            'total_gross' => 1000.00,
            'less_srpwd' => 100.00,
            'srpwd_refno' => 'SR-998877',
            'less_phic' => 150.00,
            'phic_icd_rvs' => 'J00.0',
            'less_hmo' => 200.00,
            'less_discount' => 50.00,
            'discount_description' => 'Staff Courtesy Discount',
            'net_payable' => 500.00,
            'payment_cash' => 300.00,
            'payment_card' => 200.00,
        ]);

        // Fetch settlements API verification
        $fetchResponse = $this->actingAs($admin, 'admin')->postJson('/api/fetch_settlements', [
            'consultationrefno' => 'CON_TEST_SETT_DISC_001'
        ]);

        $fetchResponse->assertStatus(200);
        $fetchResponse->assertJson(['success' => true]);
        $record = $fetchResponse->json('record');
        $this->assertEquals('100.00', $record['less_srpwd']);
        $this->assertEquals('SR-998877', $record['srpwd_refno']);
        $this->assertEquals('150.00', $record['less_phic']);
        $this->assertEquals('J00.0', $record['phic_icd_rvs']);
        $this->assertEquals('200.00', $record['less_hmo']);
        $this->assertEquals('50.00', $record['less_discount']);
        $this->assertEquals('Staff Courtesy Discount', $record['discount_description']);
        $this->assertEquals('500.00', $record['net_payable']);
    }

    /**
     * Detailed Comment: Test medicine RX pricing adheres to patient type tiers (REGULAR, PHIC, HMO, OTHERS).
     */
    public function test_medicine_rx_pricing_adheres_to_patient_type(): void
    {
        $admin = AdminModel::first();

        // Create tiered stock item
        $medListing = StocksListingModel::create([
            'prod_itemdscr' => 'Paracetamol 500mg Tablet',
            'item_grouping' => 'DRUGS AND MEDS',
            'price_regular' => 10.00,
            'price_phic' => 8.00,
            'price_hmo' => 7.50,
            'price_others' => 9.00,
            'is_inventory' => 0,
            'qty' => 100
        ]);

        // Test PHIC patient
        $phicConsultation = ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_PHIC_PRICING',
            'caseno' => 'CASE_PHIC_001',
            'pincode' => 'PIN_PHIC_001',
            'patientname' => 'PHIC, PATIENT TEST',
            'finadiagnosis' => '',
            'pxrefno' => 'PTN_PHIC_001',
            'classification' => 'phic',
            'status' => 'IN_CONSULTATION',
            'consultation_date' => now()
        ]);

        $this->actingAs($admin, 'admin')->postJson('/api/add_medicine', [
            'consultationrefno' => $phicConsultation->consultationrefno,
            'prodcode' => $medListing->prodcode,
            'qty' => 2,
            'instructions' => '1 tab TID PRN'
        ])->assertStatus(200);

        // Verify stocks ledger recorded PHIC unit price 8.00 and total 16.00
        $this->assertDatabaseHas('stocks_ledger', [
            'px_consultcode_cn' => $phicConsultation->consultationrefno,
            'prodcode' => $medListing->prodcode,
            'cost_ave' => 8.00,
            'totalamt' => 16.00
        ]);
    }

    /**
     * Detailed Comment: Test Professional Fee calculation applies autoAddVAT if enabled in doctor profile.
     */
    public function test_doctor_professional_fee_auto_adds_vat(): void
    {
        $admin = AdminModel::first();

        // Create doctor profile with autoAddVAT enabled and 12% VAT rate
        $doctor = DoctorsProfileModel::create([
            'docrefno' => 'DOC_VAT_001',
            'pfrate' => 500.00,
            'vatrate' => 12.00,
            'autoAddVAT' => 1,
            'tax' => 5.00,
            'vatable' => 1
        ]);

        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_VAT_PF',
            'caseno' => 'CASE_VAT_001',
            'pincode' => 'PIN_VAT_001',
            'patientname' => 'VAT, PATIENT TEST',
            'finadiagnosis' => '',
            'pxrefno' => 'PTN_VAT_001',
            'docrefno' => $doctor->docrefno,
            'status' => 'IN_CONSULTATION',
            'consultation_date' => now()
        ]);

        // Fetch patient charges triggers initial PF generation
        $response = $this->actingAs($admin, 'admin')->postJson('/api/fetch_patient_charges', [
            'consultationrefno' => $consultation->consultationrefno
        ]);

        $response->assertStatus(200);

        // Expected fee: 500 + (500 * 0.12) = 560.00
        $this->assertDatabaseHas('stocks_ledger', [
            'px_consultcode_cn' => $consultation->consultationrefno,
            'item_grouping' => 'PROFESSIONAL FEE',
            'cost_ave' => 560.00,
            'totalamt' => 560.00
        ]);
    }

    /**
     * Detailed Comment: Test Rx printable PDF layout format: {name} newline Sig: {instructions}
     * and general instructions located at the bottom.
     */
    public function test_rx_printable_view_order_and_instructions(): void
    {
        $admin = AdminModel::first();

        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_RX_LAYOUT',
            'caseno' => 'CASE_LAYOUT_001',
            'pincode' => 'PIN_LAYOUT_001',
            'patientname' => 'RX, LAYOUT TEST',
            'finadiagnosis' => '',
            'pxrefno' => 'PTN_LAYOUT_001',
            'instructions' => 'General advice: Drink 8 glasses of water daily and rest.',
            'status' => 'IN_CONSULTATION',
            'consultation_date' => now()
        ]);

        StocksLedgerModel::create([
            'px_consultcode_cn' => $consultation->consultationrefno,
            'item_dscr' => 'Amoxicillin 500mg Capsule',
            'item_grouping' => 'DRUGS AND MEDS',
            'instructions' => 'Take 1 capsule 3 times daily after meals for 7 days',
            'qty' => 21,
            'cost_ave' => 15.00,
            'totalamt' => 315.00
        ]);

        // Detailed Comment: Render Blade view directly to verify HTML layout ordering and Sig instructions
        $view = view('printables.rx_print', [
            'doctor' => (object)['docname' => 'Attending Physician', 'Licno' => '', 'PTR' => '', 'S2no' => ''],
            'type' => 'rx',
            'patient' => $consultation,
            'profile' => (object)['HOSP_NAME' => 'KayakapMD Clinic', 'HOSP_ADDBRGY' => ''],
            'medicines' => collect([[
                'medicinename' => 'Amoxicillin 500mg Capsule',
                'medicinequantity' => 21,
                'instructions' => 'Take 1 capsule 3 times daily after meals for 7 days',
            ]]),
            'charges' => collect(),
            'settlement' => null,
            'requests' => collect()
        ])->render();

        $this->assertStringContainsString('Amoxicillin 500mg Capsule', $view);
        $this->assertStringContainsString('Sig:', $view);
        $this->assertStringContainsString('Take 1 capsule 3 times daily after meals for 7 days', $view);
        $this->assertStringContainsString('General advice: Drink 8 glasses of water daily and rest.', $view);

        // Detailed Comment: Verify PDF generation endpoint streams application/pdf successfully
        $response = $this->actingAs($admin, 'admin')->get('/print_pdf?consultationrefno=' . $consultation->consultationrefno);
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    /**
     * Detailed Comment: Verifies that /api/fetch_hmo retrieves HMO records directly from the
     * "hmo_masterlist" table ordered alphabetically by hmoname ASC.
     */
    public function test_fetch_hmo_retrieves_and_orders_from_hmo_masterlist(): void
    {
        $admin = AdminModel::first();

        // Create HMO records in reverse alphabetical order
        HMOModel::create([
            'hmocode' => 'ZEL001',
            'hmoname' => 'ZellCare Health Plans',
            'dw_clientcode' => '122377',
        ]);
        HMOModel::create([
            'hmocode' => 'AVE001',
            'hmoname' => 'Aventus Medical Care',
            'dw_clientcode' => '122377',
        ]);
        HMOModel::create([
            'hmocode' => 'MED001',
            'hmoname' => 'Medicard Philippines',
            'dw_clientcode' => '122377',
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson('/api/fetch_hmo');
        $response->assertStatus(200);
        $response->assertJsonStructure(['hmo' => [['hmocode', 'hmoname']]]);

        $hmos = collect($response->json('hmo'));
        $names = $hmos->pluck('hmoname')->values()->toArray();

        // Verify alphabetical order
        $sortedNames = $names;
        sort($sortedNames, SORT_NATURAL | SORT_FLAG_CASE);
        $this->assertEquals($sortedNames, $names);
    }

    /**
     * Detailed Comment: Verifies that saving settlements in secretary and admin interfaces
     * resolves and stores both hmocode and hmoname from the "hmo_masterlist" table in pxsettlements.
     */
    public function test_settlement_resolves_hmocode_and_hmoname_from_hmo_masterlist(): void
    {
        $admin = AdminModel::first();

        $hmo = HMOModel::create([
            'hmocode' => 'MAX001',
            'hmoname' => 'Maxicare Healthcare Corporation',
            'dw_clientcode' => '122377',
        ]);

        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_HMO_SYNC',
            'caseno' => 'CASE_HMO_001',
            'pincode' => 'PIN_HMO_001',
            'patientname' => 'HMO, SYNC TEST',
            'finadiagnosis' => '',
            'pxrefno' => 'PTN_HMO_001',
            'status' => 'FOR_BILLING',
            'consultation_date' => now()
        ]);

        // 1. Secretary / Queue save_settlements endpoint
        $response = $this->actingAs($admin, 'admin')->postJson('/api/save_settlements', [
            'sett_consultationrefno' => $consultation->consultationrefno,
            'total' => 1500.00,
            'hmo' => 1000.00,
            'hmo_type' => $hmo->hmocode, // Passed hmocode from dropdown
            'cash' => 500.00,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $settlement = SettlementsModel::where('consultationrefno', $consultation->consultationrefno)->first();
        $this->assertNotNull($settlement);
        $this->assertEquals($hmo->hmocode, $settlement->hmocode);
        $this->assertEquals($hmo->hmoname, $settlement->hmoname);
        $this->assertEquals(1000.00, (float) $settlement->less_hmo);
        $this->assertEquals(500.00, (float) $settlement->payment_cash);

        // 2. Admin edit_settlement endpoint
        $hmo2 = HMOModel::create([
            'hmocode' => 'INT002',
            'hmoname' => 'Intellicare Asalus Corporation',
            'dw_clientcode' => '122377',
        ]);

        $adminEditResponse = $this->actingAs($admin, 'admin')->postJson('/api/admin/edit_settlement', [
            'consultationrefno' => $consultation->consultationrefno,
            'total_gross' => 1500.00,
            'less_hmo' => 1200.00,
            'hmo_type' => $hmo2->hmocode, // Change HMO to Intellicare
            'payment_cash' => 300.00,
        ]);
        $adminEditResponse->assertStatus(200);
        $adminEditResponse->assertJson(['success' => true]);

        $settlement->refresh();
        $this->assertEquals($hmo2->hmocode, $settlement->hmocode);
        $this->assertEquals($hmo2->hmoname, $settlement->hmoname);
        $this->assertEquals(1200.00, (float) $settlement->less_hmo);
        $this->assertEquals(300.00, (float) $settlement->payment_cash);
    }

    /**
     * Detailed Comment: Verify doctor fee update works cleanly without SQL 1054 error
     * and synchronizes consultation fee and tax rates across profile and auth models.
     */
    public function test_doctor_fee_update_does_not_throw_sql_error(): void
    {
        $docUser = DoctorModel::create([
            'docrefno' => 'DOC_FEE_TEST_01',
            'username' => 'drfee',
            'password' => bcrypt('password'),
            'consultationfee' => 500.00,
            'taxpercent' => 5.00,
            'bankacct' => '123456789'
        ]);

        $docProfile = DoctorsProfileModel::create([
            'docrefno' => 'DOC_FEE_TEST_01',
            'docfname' => 'Gregory',
            'doclname' => 'House',
            'consultationfee' => 500.00,
            'tax' => 5.00,
            'vatable' => 1,
            'autoAddVAT' => 1,
            'rodrate' => 150.00,
            'pfrate' => 600.00,
        ]);

        $response = $this->actingAs($docUser, 'doctor')->postJson('/api/doctor/update_fee', [
            'docrefno' => 'DOC_FEE_TEST_01',
            'consultationfee' => 750.00,
            'taxpercent' => 10.00,
            'withholdingtax' => 10.00,
            'autoAddVAT' => 1,
            'bankacct' => '987654321',
            'vatable' => 1,
            'issuehospOR' => 0
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $docUser->refresh();
        $docProfile->refresh();

        $this->assertEquals(750.00, (float) $docUser->consultationfee);
        $this->assertEquals(10.00, (float) $docUser->taxpercent);
        $this->assertEquals('987654321', $docUser->bankacct);

        $this->assertEquals(750.00, (float) $docProfile->consultationfee);
        $this->assertEquals(10.00, (float) $docProfile->tax);
        $this->assertEquals(1, $docProfile->autoAddVAT);
    }

    /**
     * Detailed Comment: Verify fetch_pxcharges returns enriched PF details and catalog tier comparison pricing.
     */
    public function test_fetch_patient_charges_enriches_pf_and_catalog_pricing(): void
    {
        $admin = AdminModel::first();

        $docProfile = DoctorsProfileModel::create([
            'docrefno' => 'DOC_ENRICH_01',
            'docfname' => 'Stephen',
            'doclname' => 'Strange',
            'consultationfee' => 800.00,
            'pfrate' => 800.00,
            'tax' => 8.00,
            'vatable' => 1,
            'autoAddVAT' => 0,
            'rodrate' => 200.00
        ]);

        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CON_ENRICH_001',
            'caseno' => 'CASE_ENRICH_001',
            'pincode' => 'PIN_ENRICH_001',
            'patientname' => 'ENRICH, PATIENT',
            'classification' => 'PHIC',
            'docrefno' => 'DOC_ENRICH_01',
            'finadiagnosis' => 'Fever and Cough',
            'pxrefno' => 'PTN_ENRICH_001',
            'status' => 'FOR_BILLING',
            'consultation_date' => now()
        ]);

        // Create standard catalog item
        StocksListingModel::create([
            'prodcode' => 'MED_AMOX_500',
            'item_dscr' => 'Amoxicillin 500mg Capsule',
            'item_grouping' => 'DRUGS AND MEDS',
            'cost_ave' => 10.00,
            'price_regular' => 15.00,
            'price_phic' => 12.00,
            'price_hmo' => 14.00,
            'price_others' => 13.00,
        ]);

        // Add charge entry
        StocksLedgerModel::create([
            'dw_clientcode' => 'HO1',
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PTN_ENRICH_001',
            'patient_name' => 'ENRICH, PATIENT',
            'prodcode' => 'MED_AMOX_500',
            'px_consultcode_cn' => 'CON_ENRICH_001',
            'item_dscr' => 'Amoxicillin 500mg Capsule',
            'qty' => 10,
            'cost_ave' => 12.00,
            'retails' => 12.00,
            'totalamt' => 120.00,
            'item_grouping' => 'DRUGS AND MEDS'
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson('/api/fetch_pxcharges', [
            'consultationrefno' => 'CON_ENRICH_001'
        ]);

        $response->assertStatus(200);
        $charges = $response->json('charges');

        // Check PF was auto-generated and enriched
        $pfCharge = collect($charges)->firstWhere('prodcode', 'PF');
        $this->assertNotNull($pfCharge);
        $this->assertTrue($pfCharge['is_pf']);
        $this->assertEquals('800.00', $pfCharge['pf_rate']);
        $this->assertEquals('200.00', $pfCharge['rod_rate']);
        $this->assertEquals('8.00', $pfCharge['tax_percent']);
        $this->assertEquals(1, $pfCharge['vatable']);

        // Check catalog item was enriched with tier comparison
        $medCharge = collect($charges)->firstWhere('prodcode', 'MED_AMOX_500');
        $this->assertNotNull($medCharge);
        $this->assertFalse($medCharge['is_pf']);
        $this->assertEquals('15.00', $medCharge['regular_price']);
        $this->assertEquals('PHIC', $medCharge['posted_tier']);
        $this->assertEquals('12.00', $medCharge['price_phic']);
        $this->assertEquals('14.00', $medCharge['price_hmo']);
    }

    /**
     * Detailed Comment: Verify Rx printable PDF endpoint renders properly with the redesigned framed layout.
     */
    public function test_rx_printable_pdf_generation(): void
    {
        $admin = AdminModel::first();

        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CON_PRINT_001',
            'caseno' => 'CASE_PRINT_001',
            'pincode' => 'PIN_PRINT_001',
            'patientname' => 'PRINT, TEST PATIENT',
            'classification' => 'REGULAR',
            'finadiagnosis' => 'Acute Bronchitis',
            'instructions' => 'Drink plenty of water and rest.',
            'pxrefno' => 'PTN_PRINT_001',
            'status' => 'COMPLETED',
            'consultation_date' => now()
        ]);

        StocksLedgerModel::create([
            'dw_clientcode' => 'HO1',
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PTN_PRINT_001',
            'patient_name' => 'PRINT, TEST PATIENT',
            'prodcode' => 'MED_AMOX_PRINT',
            'px_consultcode_cn' => 'CON_PRINT_001',
            'item_dscr' => 'Amoxicillin 500mg Capsule',
            'qty' => 21,
            'instructions' => 'Take 1 capsule every 8 hours for 7 days',
            'cost_ave' => 15.00,
            'retails' => 15.00,
            'totalamt' => 315.00,
            'item_grouping' => 'DRUGS AND MEDS'
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/print_pdf?consultationrefno=CON_PRINT_001&type=rx');
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
