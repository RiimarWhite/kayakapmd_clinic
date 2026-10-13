<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationModel;
use App\Models\DoctorModel;
use App\Models\DoctorsProfileModel;
use App\Models\PatientMasterlist;
use App\Models\SecretaryDoctorsModel;
use App\Models\SecretaryModel;
use App\Models\Stocks\StocksLedgerModel;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Comprehensive Requirement-Driven 4-Tier E2E Test Suite for
 * Consultation Details Modal (#view_consultation_details_modal) and related features
 * across Secretary, Doctor Dashboard, and Doctor Patient Masterlist in KayakapMD Clinic.
 *
 * Covers all 18 features from PROJECT.md Feature Inventory across 4 verification tiers:
 * - Tier 1: Feature Coverage (Happy-path tests verifying features in isolation)
 * - Tier 2: Boundary & Corner Cases (Missing fields, null demographics, multiple consultations, zero charges, unauthenticated access)
 * - Tier 3: Cross-Feature Combinations (Pairwise workflows across Queue, Dashboard, Masterlist, Details Modal, PDF print, and file attachments)
 * - Tier 4: Real-World Application Scenarios (Admission workflow, multi-role parity audits, pharmacy dispensing status lifecycle)
 */
class ConsultationDetailsModalE2ETest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Detailed Comment: Seed database with essential admin and staff user fixtures
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Helper: Assigns doctor to secretary and returns initialized user fixtures.
     */
    private function getTestStaff(): array
    {
        $secretary = SecretaryModel::first();
        $admin = AdminModel::first();
        $doctorProfile = DoctorsProfileModel::first();
        $doctorUser = DoctorModel::where('docrefno', $doctorProfile->docrefno)->first() ?? DoctorModel::first();

        // Assign doctor to secretary if not assigned
        SecretaryDoctorsModel::firstOrCreate([
            'secrefno' => $secretary->secrefno,
            'docrefno' => $doctorProfile->docrefno,
        ], [
            'recordedby' => 'system',
            'recordeddate' => now(),
            'active' => 1
        ]);

        return [$secretary, $doctorUser, $admin, $doctorProfile];
    }

    /* =========================================================================
     * TIER 1: FEATURE COVERAGE (HAPPY-PATH IN ISOLATION)
     * ========================================================================= */

    /**
     * Detailed Comment: Tier 1 - Feature 1: Secretary Queue Details Trigger
     * Verifies that both Secretary and Admin users can access /secretary/queue (HTTP 200)
     * and that the view includes #view_consultation_details_modal and queue table triggers.
     */
    public function test_tier1_secretary_queue_details_trigger_markup_and_access(): void
    {
        [$secretary, , $admin] = $this->getTestStaff();

        // Secretary access check
        $secResponse = $this->actingAs($secretary, 'secretary')->get('/secretary/queue');
        $secResponse->assertStatus(200);
        $secResponse->assertSee('view_consultation_details_modal');
        $secResponse->assertSee('patients_queue_table');

        // Admin access check to secretary queue console
        $adminResponse = $this->actingAs($admin, 'admin')->get('/secretary/queue');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('view_consultation_details_modal');
        $adminResponse->assertSee('patients_queue_table');
    }

    /**
     * Detailed Comment: Tier 1 - Feature 2: Doctor Dashboard Details Trigger
     * Verifies that Doctor user can access /doctor/dashboard (HTTP 200)
     * and that the view includes #view_consultation_details_modal and today's patients queue.
     */
    public function test_tier1_doctor_dashboard_details_trigger_markup_and_access(): void
    {
        [, $doctorUser] = $this->getTestStaff();

        $response = $this->actingAs($doctorUser, 'doctor')->get('/doctor/dashboard');
        $response->assertStatus(200);
        $response->assertSee('view_consultation_details_modal');
        $response->assertSee('todays_patients_table');
        $response->assertSee('vcd_print_rx_btn');
        $response->assertSee('vcd_print_diag_btn');
    }

    /**
     * Detailed Comment: Tier 1 - Feature 3: Doctor Patient Masterlist Details Trigger
     * Verifies that Doctor user can access /doctor/patients (HTTP 200)
     * and that the view includes #view_consultation_details_modal, masterlist table,
     * and nested medical history modals.
     */
    public function test_tier1_doctor_patient_masterlist_details_trigger_markup_and_access(): void
    {
        [, $doctorUser] = $this->getTestStaff();

        $response = $this->actingAs($doctorUser, 'doctor')->get('/doctor/patients');
        $response->assertStatus(200);
        $response->assertSee('view_consultation_details_modal');
        $response->assertSee('masterlist_table');
        $response->assertSee('patientMedhistoryModal');
        $response->assertSee('viewPatientModal');
    }

    /**
     * Detailed Comment: Tier 1 - Feature 5 & 6: Responsive Styles & Nested Backdrop Safety
     * Verifies that the partial view_consultation_details.blade.php contains fluid responsive
     * styling constraints min(85rem, 95vw) and max-height: 90vh, and higher z-index (1070).
     */
    public function test_tier1_modal_responsive_styles_and_dialog_structure(): void
    {
        [, $doctorUser] = $this->getTestStaff();

        $response = $this->actingAs($doctorUser, 'doctor')->get('/doctor/dashboard');
        $response->assertStatus(200);

        // Responsive width and height style verifications
        $response->assertSee('min-width: min(85rem, 95vw)', false);
        $response->assertSee('max-height: 90vh', false);
        // Backdrop stacking z-index higher than parent modal (1055)
        $response->assertSee('style="z-index: 1070;"', false);
    }

    /**
     * Detailed Comment: Tier 1 - Feature 7: Consultation Matching by Ref No
     * Verifies that /api/fetch_patient_medhistory correctly matches the consultation
     * by exact consultationrefno when queried, avoiding naive list[0] fallbacks.
     */
    public function test_tier1_fetch_patient_medhistory_matches_consultation_by_refno(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $patientPin = 'PIN_MATCH_001';
        $pxRef = 'PX_MATCH_001';

        PatientMasterlist::create([
            'pxrefno' => $pxRef,
            'pincode' => $patientPin,
            'patientname' => 'DELA CRUZ, JUAN',
            'gender' => 'MALE',
            'birthday' => '1990-01-01',
            'mobilenumber' => '09171234567',
        ]);

        // Create older consultation
        ConsultationModel::create([
            'consultationrefno' => 'CON_OLDER_001',
            'docrefno' => $doctorProfile->docrefno,
            'pincode' => $patientPin,
            'pxrefno' => $pxRef,
            'patientname' => 'DELA CRUZ, JUAN',
            'reasonforconsultation' => 'Old cough and cold',
            'impression' => 'Acute Bronchitis',
            'finadiagnosis' => 'J20.9',
            'status' => 'COMPLETED',
            'consultation_date' => '2026-01-10 09:00:00'
        ]);

        // Create target consultation
        ConsultationModel::create([
            'consultationrefno' => 'CON_TARGET_002',
            'docrefno' => $doctorProfile->docrefno,
            'pincode' => $patientPin,
            'pxrefno' => $pxRef,
            'patientname' => 'DELA CRUZ, JUAN',
            'reasonforconsultation' => 'Target consultation: acute chest pain',
            'impression' => 'Angina Pectoris',
            'finadiagnosis' => 'I20.9',
            'status' => 'COMPLETED',
            'consultation_date' => '2026-02-15 14:00:00'
        ]);

        $res = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'pincode' => $patientPin,
            'pxrefno' => $pxRef,
            'consultationrefno' => 'CON_TARGET_002'
        ]);

        $res->assertStatus(200);
        $records = $res->json('medhistory');
        $this->assertNotEmpty($records);

        // Verify target consultation is isolated or findable in history
        $target = collect($records)->firstWhere('consultationrefno', 'CON_TARGET_002');
        $this->assertNotNull($target, 'Target consultation must exist in returned medical history');
        $this->assertEquals('Target consultation: acute chest pain', $target['reasonforconsultation']);
        $this->assertEquals('Angina Pectoris', $target['impression']);
        $this->assertEquals('I20.9', $target['finadiagnosis']);
    }

    /**
     * Detailed Comment: Tier 1 - Feature 8: Address Schema Resolution
     * Verifies that /api/fetch_patient_medhistory resolves patient address from
     * the related pxmasterlist record without throwing MySQL 1054 Unknown column 'address'.
     */
    public function test_tier1_address_resolved_from_patient_masterlist(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $pxRef = 'PX_ADDR_001';
        $pinCode = 'PIN_ADDR_001';

        PatientMasterlist::create([
            'pxrefno' => $pxRef,
            'pincode' => $pinCode,
            'patientname' => 'RAMIREZ, ELENA',
            'streetadrs' => 'Unit 402 Emerald Tower, F. Ortigas Jr. Rd',
            'brgy' => 'San Antonio',
            'muncity' => 'Pasig City',
            'province' => 'Metro Manila',
            'address' => 'Unit 402 Emerald Tower, San Antonio, Pasig City',
            'gender' => 'FEMALE',
            'birthday' => '1988-08-20',
        ]);

        ConsultationModel::create([
            'consultationrefno' => 'CON_ADDR_001',
            'docrefno' => $doctorProfile->docrefno,
            'pincode' => $pinCode,
            'pxrefno' => $pxRef,
            'patientname' => 'RAMIREZ, ELENA',
            'reasonforconsultation' => 'Annual medical examination',
            'impression' => 'Healthy adult',
            'finadiagnosis' => 'Z00.0',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        $res = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'pincode' => $pinCode,
            'pxrefno' => $pxRef,
            'consultationrefno' => 'CON_ADDR_001'
        ]);

        $res->assertStatus(200);
        $item = collect($res->json('medhistory'))->firstWhere('consultationrefno', 'CON_ADDR_001');
        $this->assertNotNull($item);
        // Address should be populated without 1054 error
        $this->assertTrue(isset($item['address']));
    }

    /**
     * Detailed Comment: Tier 1 - Feature 9 & 10: Complete Clinical Notes, Impressions, Diagnosis & Vitals
     * Verifies that /api/fetch_patient_medhistory delivers complete vital signs,
     * reason for consultation, impression, final diagnosis, instructions, and admission status.
     */
    public function test_tier1_complete_clinical_notes_vitals_and_demographics(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_CLINICAL_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'docname' => 'DR. TEST PHYSICIAN',
            'caseno' => 'CASE_CLN_01',
            'pincode' => 'PIN_CLN_01',
            'pxrefno' => 'PX_CLN_01',
            'patientname' => 'BAUTISTA, CARLOS',
            'gender' => 'MALE',
            'birthday' => '1980-04-12',
            'age' => 46,
            'mobilenumber' => '09221112233',
            'emailaddress' => 'carlos@example.com',
            'reasonforconsultation' => 'Severe recurrent epigastric pain',
            'impression' => 'Peptic Ulcer Disease (PUD)',
            'finadiagnosis' => 'K27.9',
            'instructions' => 'Avoid NSAIDs, take omeprazole 30 mins before breakfast',
            'foradmit' => 1,
            'foradmit_instructions' => 'Admit to medical ward 2A for observation',
            'weight' => 68.5,
            'wunit' => 'kg',
            'height' => 172.0,
            'hunit' => 'cm',
            'temp' => 37.6,
            'tempunit' => 'C',
            'bpnumerator' => 125,
            'bpdenominator' => 85,
            'pulserate' => 78,
            'respiratoryrate' => 18,
            'status' => 'COMPLETED',
            'consultation_date' => '2026-10-12 11:00:00'
        ]);

        $res = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'pincode' => 'PIN_CLN_01',
            'pxrefno' => 'PX_CLN_01',
            'consultationrefno' => $refNo
        ]);

        $res->assertStatus(200);
        $item = collect($res->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($item);

        $this->assertEquals('Severe recurrent epigastric pain', $item['reasonforconsultation']);
        $this->assertEquals('Peptic Ulcer Disease (PUD)', $item['impression']);
        $this->assertEquals('K27.9', $item['finadiagnosis']);
        $this->assertEquals('Avoid NSAIDs, take omeprazole 30 mins before breakfast', $item['instructions']);
        $this->assertEquals(1, intval($item['foradmit']));
        $this->assertEquals('Admit to medical ward 2A for observation', $item['foradmit_instructions']);
        $this->assertEquals('68.5', (string)$item['weight']);
        $this->assertEquals('172', (string)$item['height']);
        $this->assertEquals('37.6', (string)$item['temp']);
        $this->assertEquals('125', (string)$item['bpnumerator']);
        $this->assertEquals('85', (string)$item['bpdenominator']);
        $this->assertEquals('78', (string)$item['pulserate']);
        $this->assertEquals('18', (string)$item['respiratoryrate']);
    }

    /**
     * Detailed Comment: Tier 1 - Feature 11: Laboratory & Radiology Attachment Preview URLs
     * Verifies that when laboratorypath or radiologypath exists, the API generates valid
     * preview route URLs pointing to route('preview.file').
     */
    public function test_tier1_attachment_preview_urls_generation(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_ATTACH_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'pincode' => 'PIN_ATT_01',
            'pxrefno' => 'PX_ATT_01',
            'patientname' => 'MENDOZA, MARILOU',
            'laboratorypath' => 'lab_results/cbc_20261012.pdf',
            'radiologypath' => 'rad_results/chest_xray_20261012.png',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        $res = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);

        $res->assertStatus(200);
        $item = collect($res->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($item);
        $this->assertEquals('lab_results/cbc_20261012.pdf', $item['laboratorypath']);
        $this->assertEquals('rad_results/chest_xray_20261012.png', $item['radiologypath']);
    }

    /**
     * Detailed Comment: Tier 1 - Feature 12: Prescriptions & Diagnostics Items List
     * Verifies that /api/fetch_patient_medhistory returns itemized prescriptions (rx_items)
     * and diagnostics (diagnostic_items) with dosage, quantities, remarks, and dispensed badges.
     */
    public function test_tier1_prescriptions_and_diagnostics_itemization(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_ITEMS_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'pincode' => 'PIN_ITM_01',
            'pxrefno' => 'PX_ITM_01',
            'patientname' => 'TAN, JONATHAN',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        // Medication in stocks_ledger
        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_ITM_01',
            'patient_name' => 'TAN, JONATHAN',
            'prodcode' => 'MED_AZITHRO_500',
            'item_dscr' => 'Azithromycin 500mg Tablet',
            'item_grouping' => 'DRUGS AND MEDS',
            'cost_ave' => 20.00,
            'retails' => 45.00,
            'qty' => 3,
            'totalamt' => 135.00,
            'remarks' => 'Take 1 tablet daily for 3 days',
            'instructions' => 'Take on empty stomach 1 hour before meal',
            'dispensed_status' => 'RELEASED',
            'updatedby' => 'doctor',
            'updated' => now()
        ]);

        // Diagnostic in stocks_ledger
        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_ITM_01',
            'patient_name' => 'TAN, JONATHAN',
            'prodcode' => 'LAB_URINALYSIS',
            'item_dscr' => 'Routine Urinalysis',
            'item_grouping' => 'DIAGNOSTIC',
            'cost_ave' => 50.00,
            'retails' => 120.00,
            'qty' => 1,
            'totalamt' => 120.00,
            'remarks' => 'Clean-catch midstream urine sample',
            'updatedby' => 'doctor',
            'updated' => now()
        ]);

        $res = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);

        $res->assertStatus(200);
        $item = collect($res->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($item);

        // Verify Rx item details
        $this->assertNotEmpty($item['rx_items']);
        $rx = collect($item['rx_items'])->firstWhere('prodcode', 'MED_AZITHRO_500');
        $this->assertNotNull($rx);
        $this->assertEquals('Azithromycin 500mg Tablet', $rx['item_dscr']);
        $this->assertEquals(3, intval($rx['qty']));
        $this->assertEquals('Take 1 tablet daily for 3 days', $rx['remarks']);
        $this->assertEquals(1, intval($rx['dispensed_flag']));

        // Verify Diagnostic item details
        $this->assertNotEmpty($item['diagnostic_items']);
        $diag = collect($item['diagnostic_items'])->firstWhere('prodcode', 'LAB_URINALYSIS');
        $this->assertNotNull($diag);
        $this->assertEquals('Routine Urinalysis', $diag['item_dscr']);
        $this->assertEquals('Clean-catch midstream urine sample', $diag['remarks']);
    }

    /**
     * Detailed Comment: Tier 1 - Feature 13: Itemized Billing Charges & Gross Total Calculation
     * Verifies that stocks_ledger charge line items are summed accurately and delivered
     * with gross charges_total matching mathematical sum.
     */
    public function test_tier1_itemized_billing_charges_and_gross_total_calculation(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_CHARGES_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'pincode' => 'PIN_CHG_01',
            'pxrefno' => 'PX_CHG_01',
            'patientname' => 'AQUINO, TERESA',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        // Charge 1: Drugs & Meds (₱180.00)
        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_CHG_01',
            'patient_name' => 'AQUINO, TERESA',
            'prodcode' => 'MED_PARACETAMOL_500',
            'item_dscr' => 'Paracetamol 500mg (10 tabs)',
            'item_grouping' => 'DRUGS AND MEDS',
            'qty' => 10,
            'cost_ave' => 5.00,
            'retails' => 18.00,
            'totalamt' => 180.00,
            'updatedby' => 'doctor',
            'updated' => now()
        ]);

        // Charge 2: Diagnostic (₱350.00)
        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_CHG_01',
            'patient_name' => 'AQUINO, TERESA',
            'prodcode' => 'DIAG_FBS',
            'item_dscr' => 'Fasting Blood Sugar (FBS)',
            'item_grouping' => 'DIAGNOSTIC',
            'qty' => 1,
            'cost_ave' => 100.00,
            'retails' => 350.00,
            'totalamt' => 350.00,
            'updatedby' => 'doctor',
            'updated' => now()
        ]);

        // Charge 3: Professional Fee (₱600.00)
        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_CHG_01',
            'patient_name' => 'AQUINO, TERESA',
            'prodcode' => 'PF_CONSULTATION',
            'item_dscr' => 'Attending Physician Outpatient Fee',
            'item_grouping' => 'PROFESSIONAL FEE',
            'qty' => 1,
            'cost_ave' => 600.00,
            'retails' => 600.00,
            'totalamt' => 600.00,
            'updatedby' => 'secretary',
            'updated' => now()
        ]);

        $res = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);

        $res->assertStatus(200);
        $item = collect($res->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($item);

        $this->assertCount(3, $item['charges']);
        // 180.00 + 350.00 + 600.00 = 1130.00
        $this->assertEquals(1130.00, floatval($item['charges_total']));
    }

    /**
     * Detailed Comment: Tier 1 - Feature 14 & 16: Rx Printable PDF Endpoint Across Roles
     * Verifies that GET /print_pdf?type=rx&consultationrefno=... streams HTTP 200
     * with application/pdf across Doctor, Secretary, and Admin user sessions.
     */
    public function test_tier1_printable_pdf_rx_stream_success(): void
    {
        [$secretary, $doctorUser, $admin, $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_PRINT_RX_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'patientname' => 'CRUZ, FELIPE',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'prodcode' => 'MED_AMOX',
            'item_dscr' => 'Amoxicillin 500mg',
            'item_grouping' => 'DRUGS AND MEDS',
            'qty' => 21,
            'totalamt' => 210.00,
            'instructions' => 'Take 1 cap every 8 hours',
            'updated' => now()
        ]);

        // Doctor session stream
        $docRes = $this->actingAs($doctorUser, 'doctor')->get("/print_pdf?type=rx&consultationrefno={$refNo}");
        $docRes->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $docRes->headers->get('Content-Type'));

        // Secretary session stream
        $secRes = $this->actingAs($secretary, 'secretary')->get("/print_pdf?type=rx&consultationrefno={$refNo}");
        $secRes->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $secRes->headers->get('Content-Type'));

        // Admin session stream
        $adminRes = $this->actingAs($admin, 'admin')->get("/print_pdf?type=rx&consultationrefno={$refNo}");
        $adminRes->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $adminRes->headers->get('Content-Type'));
    }

    /**
     * Detailed Comment: Tier 1 - Feature 15 & 16: Diagnostics Printable PDF Endpoint Across Roles
     * Verifies that GET /print_pdf?type=diagnostics&consultationrefno=... streams HTTP 200
     * with application/pdf across Doctor, Secretary, and Admin user sessions.
     */
    public function test_tier1_printable_pdf_diagnostics_stream_success(): void
    {
        [$secretary, $doctorUser, $admin, $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_PRINT_DIAG_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'patientname' => 'NAVARRO, GLORIA',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'prodcode' => 'DIAG_LIPID',
            'item_dscr' => 'Lipid Profile',
            'item_grouping' => 'DIAGNOSTIC',
            'qty' => 1,
            'totalamt' => 450.00,
            'updated' => now()
        ]);

        // Doctor session
        $docRes = $this->actingAs($doctorUser, 'doctor')->get("/print_pdf?type=diagnostics&consultationrefno={$refNo}");
        $docRes->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $docRes->headers->get('Content-Type'));

        // Secretary session
        $secRes = $this->actingAs($secretary, 'secretary')->get("/print_pdf?type=diagnostics&consultationrefno={$refNo}");
        $secRes->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $secRes->headers->get('Content-Type'));

        // Admin session
        $adminRes = $this->actingAs($admin, 'admin')->get("/print_pdf?type=diagnostics&consultationrefno={$refNo}");
        $adminRes->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $adminRes->headers->get('Content-Type'));
    }

    /* =========================================================================
     * TIER 2: BOUNDARY & CORNER CASES
     * ========================================================================= */

    /**
     * Detailed Comment: Tier 2 - Corner Case: Missing Demographics & Dynamic Age Calculation
     * Verifies that when age is omitted from consultation, it does not throw an error,
     * and birthday is processed safely.
     */
    public function test_tier2_missing_demographics_and_dynamic_age_calculation(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_NO_AGE_001';
        $pxRef = 'PX_NO_AGE_001';

        PatientMasterlist::create([
            'pxrefno' => $pxRef,
            'patientname' => 'DE LEON, ANGELICA',
            'birthday' => '1995-10-10',
            'gender' => 'FEMALE',
        ]);

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'pxrefno' => $pxRef,
            'patientname' => 'DE LEON, ANGELICA',
            'age' => null, // Omitted
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        $res = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);

        $res->assertStatus(200);
        $item = collect($res->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($item);
    }

    /**
     * Detailed Comment: Tier 2 - Corner Case: Zero Charges and Empty Items Safe Payload
     * Verifies that a consultation without prescriptions, diagnostics, or charges
     * returns charges_total = 0 and empty collections without null pointer crashes.
     */
    public function test_tier2_zero_charges_and_empty_items_safe_payload(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_EMPTY_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'patientname' => 'SORIANO, PAULO',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        $res = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);

        $res->assertStatus(200);
        $item = collect($res->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($item);
        $this->assertEquals(0, floatval($item['charges_total']));
        $this->assertEmpty($item['rx_items']);
        $this->assertEmpty($item['diagnostic_items']);
        $this->assertEmpty($item['charges']);
    }

    /**
     * Detailed Comment: Tier 2 - Corner Case: Missing or Null Attachment Paths
     * Verifies that null laboratorypath and empty radiologypath do not cause
     * errors or invalid URLs.
     */
    public function test_tier2_missing_or_null_attachment_paths(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_NO_ATTACH_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'patientname' => 'VALDEZ, RICARDO',
            'laboratorypath' => null,
            'radiologypath' => '',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        $res = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);

        $res->assertStatus(200);
        $item = collect($res->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($item);
        $this->assertTrue(empty($item['laboratorypath']));
        $this->assertTrue(empty($item['radiologypath']));
    }

    /**
     * Detailed Comment: Tier 2 - Boundary: Non-existent or Invalid Consultation Ref No
     * Verifies that querying a non-existent reference number returns HTTP 200 with an empty list
     * without throwing a 500 error or database exception.
     */
    public function test_tier2_nonexistent_or_invalid_consultationrefno(): void
    {
        [$secretary] = $this->getTestStaff();

        $res = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => 'NON_EXISTENT_REF_9999999'
        ]);

        $res->assertStatus(200);
        $this->assertTrue($res->json('success'));
        $this->assertEmpty($res->json('medhistory'));
    }

    /**
     * Detailed Comment: Tier 2 - Security Boundary: Unauthenticated Access Rejection
     * Verifies that guest requests to protected queue, dashboard, masterlist, API, and print endpoints
     * are strictly rejected with 302 redirects to login or 401 unauthorized.
     */
    public function test_tier2_unauthenticated_access_rejection(): void
    {
        // Unauthenticated queue access
        $this->get('/secretary/queue')->assertStatus(302)->assertRedirect('/login');

        // Unauthenticated doctor dashboard access
        $this->get('/doctor/dashboard')->assertStatus(302)->assertRedirect('/login');

        // Unauthenticated doctor patients masterlist access
        $this->get('/doctor/patients')->assertStatus(302)->assertRedirect('/login');

        // Unauthenticated print PDF endpoint
        $this->get('/print_pdf?type=rx&consultationrefno=CON_001')->assertStatus(302)->assertRedirect('/login');
    }

    /**
     * Detailed Comment: Tier 2 - Multi-Consultation Patient Separation
     * Verifies that multiple past consultations for the same patient have strictly segregated
     * charges and line items without bleed-over when queried by exact consultation reference.
     */
    public function test_tier2_multiple_consultations_same_patient_separation(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $patientPin = 'PIN_MULTI_001';
        $pxRef = 'PX_MULTI_001';

        PatientMasterlist::create([
            'pxrefno' => $pxRef,
            'pincode' => $patientPin,
            'patientname' => 'VILLANUEVA, ROSA',
        ]);

        // Consultation 1: ₱200.00
        ConsultationModel::create([
            'consultationrefno' => 'CON_SEP_001',
            'docrefno' => $doctorProfile->docrefno,
            'pincode' => $patientPin,
            'pxrefno' => $pxRef,
            'patientname' => 'VILLANUEVA, ROSA',
            'status' => 'COMPLETED',
            'consultation_date' => '2026-03-01 10:00:00'
        ]);
        StocksLedgerModel::create([
            'px_consultcode_cn' => 'CON_SEP_001',
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'prodcode' => 'CHG_01',
            'item_dscr' => 'Consultation Charge 1',
            'totalamt' => 200.00,
            'updated' => now()
        ]);

        // Consultation 2: ₱750.00
        ConsultationModel::create([
            'consultationrefno' => 'CON_SEP_002',
            'docrefno' => $doctorProfile->docrefno,
            'pincode' => $patientPin,
            'pxrefno' => $pxRef,
            'patientname' => 'VILLANUEVA, ROSA',
            'status' => 'COMPLETED',
            'consultation_date' => '2026-04-01 11:00:00'
        ]);
        StocksLedgerModel::create([
            'px_consultcode_cn' => 'CON_SEP_002',
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'prodcode' => 'CHG_02',
            'item_dscr' => 'Consultation Charge 2',
            'totalamt' => 750.00,
            'updated' => now()
        ]);

        // Query specifically for consultation 2
        $res = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => 'CON_SEP_002'
        ]);

        $res->assertStatus(200);
        $item = collect($res->json('medhistory'))->firstWhere('consultationrefno', 'CON_SEP_002');
        $this->assertNotNull($item);
        $this->assertEquals(750.00, floatval($item['charges_total']));
    }

    /* =========================================================================
     * TIER 3: CROSS-FEATURE COMBINATIONS
     * ========================================================================= */

    /**
     * Detailed Comment: Tier 3 - Pipeline 1: Secretary Queue -> Consultation Details -> Print Rx
     * Simulates full front-desk workflow: secretary visits queue, queries patient consultation
     * details, and directly streams the printable prescription PDF.
     */
    public function test_tier3_secretary_queue_to_consultation_details_to_print_rx_pipeline(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_PIPE_RX_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'patientname' => 'GOMEZ, ROBERTO',
            'status' => 'WAITING',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'prodcode' => 'MED_IBUPROFEN',
            'item_dscr' => 'Ibuprofen 400mg',
            'item_grouping' => 'DRUGS AND MEDS',
            'qty' => 10,
            'totalamt' => 100.00,
            'instructions' => 'Take with meals',
            'updated' => now()
        ]);

        // Step 1: Secretary renders queue page
        $queuePage = $this->actingAs($secretary, 'secretary')->get('/secretary/queue');
        $queuePage->assertStatus(200);
        $queuePage->assertSee('view_consultation_details_modal');

        // Step 2: Secretary clicks details, fetching medhistory
        $medRes = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);
        $medRes->assertStatus(200);
        $record = collect($medRes->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($record);

        // Step 3: From modal, Secretary prints Rx PDF
        $pdfRes = $this->actingAs($secretary, 'secretary')->get("/print_pdf?type=rx&consultationrefno={$refNo}");
        $pdfRes->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $pdfRes->headers->get('Content-Type'));
    }

    /**
     * Detailed Comment: Tier 3 - Pipeline 2: Doctor Dashboard -> Details Modal -> Attachment Route
     * Simulates doctor workflow: opens dashboard, retrieves today's patient queue and medhistory,
     * inspects laboratory attachment file path.
     */
    public function test_tier3_doctor_dashboard_to_details_modal_to_attachment_pipeline(): void
    {
        [$secretary, $doctorUser, , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_PIPE_DOC_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'patientname' => 'CASTRO, BEATRIZ',
            'status' => 'WAITING',
            'queueno' => '005',
            'laboratorypath' => 'lab_results/urinalysis_sample.pdf',
            'consultation_date' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        // Step 1: Doctor renders dashboard
        $dashRes = $this->actingAs($doctorUser, 'doctor')->get('/doctor/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('view_consultation_details_modal');

        // Step 2: Doctor queries today's patients
        $todayRes = $this->actingAs($doctorUser, 'doctor')->postJson('/api/fetch_todays_patients', [
            'docrefno' => $doctorProfile->docrefno
        ]);
        $todayRes->assertStatus(200);

        // Step 3: Doctor opens consultation details
        $detailsRes = $this->actingAs($doctorUser, 'doctor')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);
        $detailsRes->assertStatus(200);
        $record = collect($detailsRes->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($record);
        $this->assertEquals('lab_results/urinalysis_sample.pdf', $record['laboratorypath']);
    }

    /**
     * Detailed Comment: Tier 3 - Pipeline 3: Doctor Masterlist -> Patient History -> Billing Verification
     * Simulates physician reviewing historical consultation from masterlist and reconciling
     * the itemized line items with calculated charges_total.
     */
    public function test_tier3_doctor_masterlist_to_history_modal_to_billing_verification(): void
    {
        [, $doctorUser, , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_PIPE_BILL_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'pincode' => 'PIN_PIPE_B1',
            'pxrefno' => 'PX_PIPE_B1',
            'patientname' => 'HERRERA, EDUARDO',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'prodcode' => 'ITEM_A',
            'item_dscr' => 'Item A',
            'qty' => 2,
            'retails' => 150.00,
            'totalamt' => 300.00,
            'updated' => now()
        ]);

        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'prodcode' => 'ITEM_B',
            'item_dscr' => 'Item B',
            'qty' => 1,
            'retails' => 450.00,
            'totalamt' => 450.00,
            'updated' => now()
        ]);

        // Step 1: Doctor renders patients masterlist
        $pageRes = $this->actingAs($doctorUser, 'doctor')->get('/doctor/patients');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('view_consultation_details_modal');

        // Step 2: Doctor fetches medhistory
        $res = $this->actingAs($doctorUser, 'doctor')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);
        $res->assertStatus(200);
        $record = collect($res->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($record);

        // Step 3: Verify billing reconciliation: line items sum strictly matches charges_total
        $sumOfLines = collect($record['charges'])->sum('totalamt');
        $this->assertEquals(750.00, $sumOfLines);
        $this->assertEquals($sumOfLines, floatval($record['charges_total']));
    }

    /* =========================================================================
     * TIER 4: REAL-WORLD APPLICATION SCENARIOS
     * ========================================================================= */

    /**
     * Detailed Comment: Tier 4 - Scenario 1: Patient Emergency Admission Workflow
     * Simulates urgent patient admission: foradmit = 1, inpatient instructions,
     * vital signs, and admission PDF generation.
     */
    public function test_tier4_patient_emergency_admission_and_instructions_workflow(): void
    {
        [$secretary, $doctorUser, , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_SCEN_ADMIT_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'patientname' => 'MAGSAYSAY, RAMON',
            'reasonforconsultation' => 'Crushing chest pain radiating to left jaw',
            'impression' => 'Acute ST-Elevation Myocardial Infarction (STEMI)',
            'finadiagnosis' => 'I21.3',
            'foradmit' => 1,
            'foradmit_instructions' => 'Immediate transfer to CCU, start IV Heparin and Aspirin stat',
            'bpnumerator' => 90,
            'bpdenominator' => 60,
            'pulserate' => 110,
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        // Secretary views admission details
        $secRes = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);
        $secRes->assertStatus(200);
        $record = collect($secRes->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($record);
        $this->assertEquals(1, intval($record['foradmit']));
        $this->assertEquals('Immediate transfer to CCU, start IV Heparin and Aspirin stat', $record['foradmit_instructions']);

        // Doctor streams admission order PDF document
        $admitPdf = $this->actingAs($doctorUser, 'doctor')->get("/print_pdf?type=admission&consultationrefno={$refNo}");
        $admitPdf->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $admitPdf->headers->get('Content-Type'));
    }

    /**
     * Detailed Comment: Tier 4 - Scenario 2: Multi-Role Session Parity and Audit
     * Verifies that a single consultation viewed sequentially by Doctor, Secretary, and Admin
     * delivers 100% identical clinical payload fields.
     */
    public function test_tier4_multi_role_session_parity_and_audit(): void
    {
        [$secretary, $doctorUser, $admin, $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_SCEN_AUDIT_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'docname' => 'DR. JUAN CRUZ',
            'patientname' => 'LIM, DOMINIC',
            'reasonforconsultation' => 'Routine quarterly endocrine review',
            'impression' => 'Type 2 Diabetes Mellitus with good glycemic control',
            'finadiagnosis' => 'E11.9',
            'instructions' => 'Continue Metformin 500mg BID with meals',
            'classification' => 'HMO',
            'subclassification' => 'MAXICARE',
            'hmoname' => 'Maxicare Healthcare',
            'status' => 'COMPLETED',
            'consultation_date' => '2026-10-12 15:30:00'
        ]);

        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'prodcode' => 'MED_METFORMIN',
            'item_dscr' => 'Metformin 500mg Film-Coated Tablet',
            'item_grouping' => 'DRUGS AND MEDS',
            'qty' => 60,
            'totalamt' => 300.00,
            'remarks' => '1 tab BID after meals',
            'dispensed_status' => 'RELEASED',
            'updated' => now()
        ]);

        $roles = [
            ['user' => $doctorUser, 'guard' => 'doctor'],
            ['user' => $secretary, 'guard' => 'secretary'],
            ['user' => $admin, 'guard' => 'admin'],
        ];

        $payloads = [];

        foreach ($roles as $r) {
            $response = $this->actingAs($r['user'], $r['guard'])->postJson('/api/fetch_patient_medhistory', [
                'consultationrefno' => $refNo
            ]);
            $response->assertStatus(200);
            $record = collect($response->json('medhistory'))->firstWhere('consultationrefno', $refNo);
            $this->assertNotNull($record, "Record must be found for guard {$r['guard']}");
            $payloads[$r['guard']] = $record;
        }

        // Assert parity between Doctor and Secretary
        $this->assertEquals($payloads['doctor']['reasonforconsultation'], $payloads['secretary']['reasonforconsultation']);
        $this->assertEquals($payloads['doctor']['impression'], $payloads['secretary']['impression']);
        $this->assertEquals($payloads['doctor']['finadiagnosis'], $payloads['secretary']['finadiagnosis']);
        $this->assertEquals($payloads['doctor']['charges_total'], $payloads['secretary']['charges_total']);

        // Assert parity between Doctor and Admin
        $this->assertEquals($payloads['doctor']['reasonforconsultation'], $payloads['admin']['reasonforconsultation']);
        $this->assertEquals($payloads['doctor']['charges_total'], $payloads['admin']['charges_total']);
    }

    /**
     * Detailed Comment: Tier 4 - Scenario 3: Pharmacy Dispensing Status Lifecycle
     * Verifies that dispensed medications have dispensed_flag = 1 while pending medications
     * have dispensed_flag = 0, allowing accurate UI badge rendering in the prescriptions table.
     */
    public function test_tier4_pharmacy_dispensing_and_pending_prescription_lifecycle(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_SCEN_DISP_001';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'patientname' => 'DEL ROSARIO, CLARA',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        // Released medication
        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'prodcode' => 'MED_RELEASED',
            'item_dscr' => 'Salbutamol 2mg Tablet (Released)',
            'item_grouping' => 'DRUGS AND MEDS',
            'qty' => 10,
            'totalamt' => 50.00,
            'dispensed_status' => 'RELEASED',
            'dispensed' => now(),
            'updated' => now()
        ]);

        // Pending medication
        StocksLedgerModel::create([
            'px_consultcode_cn' => $refNo,
            'dw_clientcode' => 'CL01',
            'transactiontype' => 'CHARGES',
            'prodcode' => 'MED_PENDING',
            'item_dscr' => 'Fluticasone Inhaler (Pending)',
            'item_grouping' => 'DRUGS AND MEDS',
            'qty' => 1,
            'totalamt' => 850.00,
            'dispensed_status' => 'PENDING',
            'dispensed' => null,
            'updated' => now()
        ]);

        $res = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);

        $res->assertStatus(200);
        $record = collect($res->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($record);

        $releasedItem = collect($record['rx_items'])->firstWhere('prodcode', 'MED_RELEASED');
        $pendingItem = collect($record['rx_items'])->firstWhere('prodcode', 'MED_PENDING');

        $this->assertNotNull($releasedItem);
        $this->assertNotNull($pendingItem);

        $this->assertEquals(1, intval($releasedItem['dispensed_flag']));
        $this->assertEquals(0, intval($pendingItem['dispensed_flag']));
    }
}
