<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationModel;
use App\Models\DoctorModel;
use App\Models\PatientMasterlist;
use App\Models\PxMedicalHistoryModel;
use App\Models\SettlementsModel;
use App\Models\UserModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Feature test suite verifying Settlements enhancements (LOA ref #, PhilHealth Yakap Co-Pay),
 * Permanent Medical History API persistence and retrieval, Secretary Queue financial reporting,
 * and half-A4 landscape printable layouts.
 */
class SettlementsAndConsoleEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Verifies saving and fetching permanent patient medical history
     * via /api/save_patient_medical_history and /api/fetch_patient_medical_history.
     */
    public function test_save_and_fetch_patient_permanent_medical_history(): void
    {
        $admin = AdminModel::first();

        // 1. Create patient record
        $patient = PatientMasterlist::create([
            'pxrefno' => 'PX_MED_TEST_001',
            'pincode' => 'PIN_MED_001',
            'pxfirstname' => 'Juan',
            'pxlastname' => 'Dela Cruz',
            'pxbday' => '1990-05-15',
            'pxgender' => 'Male',
        ]);

        // 2. Save medical history
        $savePayload = [
            'pxrefno' => 'PX_MED_TEST_001',
            'pincode' => 'PIN_MED_001',
            'allergies' => 'Penicillin, Seafoods',
            'injections_immunization' => 'Tetanus Toxoid, Flu Vaccine 2025',
            'past_medical_history' => 'Hypertension (3 years)',
            'surgical_history' => 'Appendectomy (2015)',
            'family_history' => 'Maternal Diabetes',
            'maintenance_medications' => 'Amlodipine 5mg OD',
            'notes' => 'Patient requires BP monitoring prior to procedures.',
        ];

        $saveResponse = $this->actingAs($admin, 'admin')->postJson('/api/save_patient_medical_history', $savePayload);
        $saveResponse->assertStatus(200);
        $saveResponse->assertJson(['success' => true]);

        // Verify database persistence
        $this->assertDatabaseHas('pxmedicalhistory', [
            'pxrefno' => 'PX_MED_TEST_001',
            'allergies' => 'Penicillin, Seafoods',
            'maintenance_medications' => 'Amlodipine 5mg OD',
        ]);

        // 3. Fetch medical history by pxrefno
        $fetchResponse = $this->actingAs($admin, 'admin')->postJson('/api/fetch_patient_medical_history', [
            'pxrefno' => 'PX_MED_TEST_001'
        ]);
        $fetchResponse->assertStatus(200);
        $fetchResponse->assertJson([
            'success' => true,
            'data' => [
                'allergies' => 'Penicillin, Seafoods',
                'past_medical_history' => 'Hypertension (3 years)',
                'notes' => 'Patient requires BP monitoring prior to procedures.',
            ]
        ]);

        // 4. Fetch medical history by pincode
        $fetchByPinResponse = $this->actingAs($admin, 'admin')->postJson('/api/fetch_patient_medical_history', [
            'pincode' => 'PIN_MED_001'
        ]);
        $fetchByPinResponse->assertStatus(200);
        $fetchByPinResponse->assertJson([
            'success' => true,
            'data' => [
                'allergies' => 'Penicillin, Seafoods'
            ]
        ]);
    }

    /**
     * Detailed Comment: Verifies settlements saving with HMO Letter of Authorization (LOA) reference number,
     * PhilHealth Yakap switch, and Co-Pay input value.
     */
    public function test_save_settlements_with_loa_and_yakap_copay(): void
    {
        $admin = AdminModel::first();

        // Create consultation
        $consult = ConsultationModel::create([
            'consultationrefno' => 'CONSUL_SETT_001',
            'caseno' => 'CASE_SETT_01',
            'pxrefno' => 'PX_SETT_001',
            'pincode' => 'PIN_SETT_001',
            'patientname' => 'Maria Clara',
            'consultation_date' => '2026-10-08 10:00:00',
            'consultation_time' => '10:00',
            'status' => 'PENDING',
        ]);

        $settlementData = [
            'consultationrefno' => 'CONSUL_SETT_001',
            'total' => '1500.00',
            'phic' => '300.00',
            'phic_icd_rvs' => 'ICD-10 J06.9',
            'hmo_type' => 'Maxicare',
            'hmo_loa_no' => 'LOA-MAX-2026-99881',
            'hmo' => '500.00',
            'is_srpwd' => '1',
            'less_srpwd' => '100.00',
            'srpwd_id' => 'PWD-778899',
            'is_philhealth_yakap' => '1',
            'cash' => '400.00',
            'copay' => '200.00',
            'card' => '0.00',
        ];

        $response = $this->actingAs($admin, 'admin')->postJson('/api/save_settlements', $settlementData);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify settlement persistence
        $this->assertDatabaseHas('pxsettlements', [
            'consultationrefno' => 'CONSUL_SETT_001',
            'hmo_loa_no' => 'LOA-MAX-2026-99881',
            'is_philhealth_yakap' => 1,
            'copay' => 200.00,
            'payment_cash' => 400.00,
            'less_phic' => 300.00,
            'less_hmo' => 500.00,
        ]);
    }

    /**
     * Detailed Comment: Verifies consolidated queue financial summary calculations across multiple patients
     * for a given queue date via /api/fetch_queue_financial_summary.
     */
    public function test_fetch_queue_financial_summary_metrics(): void
    {
        $admin = AdminModel::first();
        $queueDate = '2026-10-08';

        // Patient 1: Total 1,000 | PHIC 200 | Cash 500 | Co-Pay 300 (Paid in full)
        $consult1 = ConsultationModel::create([
            'consultationrefno' => 'CONSUL_FIN_001',
            'caseno' => 'CASE_FIN_01',
            'pxrefno' => 'PX_FIN_001',
            'patientname' => 'Patient One',
            'consultation_date' => "$queueDate 09:00:00",
            'status' => 'COMPLETED',
        ]);
        SettlementsModel::create([
            'consultationrefno' => 'CONSUL_FIN_001',
            'total_gross' => 1000.00,
            'less_phic' => 200.00,
            'net_payable' => 800.00,
            'payment_cash' => 500.00,
            'is_philhealth_yakap' => 1,
            'copay' => 300.00,
        ]);

        // Patient 2: Total 2,000 | HMO 1,000 | Senior 200 | Cash 800 (Paid in full)
        $consult2 = ConsultationModel::create([
            'consultationrefno' => 'CONSUL_FIN_002',
            'caseno' => 'CASE_FIN_02',
            'pxrefno' => 'PX_FIN_002',
            'patientname' => 'Patient Two',
            'consultation_date' => "$queueDate 10:00:00",
            'status' => 'PENDING',
        ]);
        SettlementsModel::create([
            'consultationrefno' => 'CONSUL_FIN_002',
            'total_gross' => 2000.00,
            'less_hmo' => 1000.00,
            'hmo_loa_no' => 'LOA-2026-1122',
            'less_srpwd' => 200.00,
            'net_payable' => 800.00,
            'payment_cash' => 800.00,
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson('/api/fetch_queue_financial_summary', [
            'date' => $queueDate
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'date' => $queueDate,
            'summary' => [
                'total_patients' => 2,
                'gross_total' => 3000.00,
                'total_phic' => 200.00,
                'total_hmo' => 1000.00,
                'total_srpwd' => 200.00,
                'total_cash' => 1300.00,
                'total_copay' => 300.00,
                'total_balance' => 0.00,
            ]
        ]);
    }

    /**
     * Detailed Comment: Verifies consolidated daily financial report printable PDF generation route.
     */
    public function test_print_financial_report_pdf(): void
    {
        $admin = AdminModel::first();
        $queueDate = '2026-10-08';

        $response = $this->actingAs($admin, 'admin')->get("/print_financial_report?date={$queueDate}");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    /**
     * Detailed Comment: Verifies prescription and diagnostic requests printable routes render A5 landscape PDFs without error.
     */
    public function test_rx_and_diagnostics_printables(): void
    {
        $admin = AdminModel::first();

        $consult = ConsultationModel::create([
            'consultationrefno' => 'CONSUL_PRINT_001',
            'caseno' => 'CASE_PRINT_01',
            'pxrefno' => 'PX_PRINT_001',
            'patientname' => 'Testing Patient',
            'consultation_date' => '2026-10-08 11:00:00',
            'status' => 'COMPLETED',
        ]);

        // Test Rx printable endpoint
        $rxResponse = $this->actingAs($admin, 'admin')->get("/print_pdf?type=rx&consultationrefno=CONSUL_PRINT_001");
        $rxResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $rxResponse->headers->get('content-type'));

        // Test Diagnostics printable endpoint
        $diagResponse = $this->actingAs($admin, 'admin')->get("/doctor/print_diagnostics?consultationrefno=CONSUL_PRINT_001");
        $diagResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $diagResponse->headers->get('content-type'));
    }

    /**
     * Detailed Comment: Verifies Secretary queue page renders the allergy alert banner,
     * Medical History tab, financial summary card, and responsive layout without error.
     */
    public function test_secretary_queue_page_renders_new_elements(): void
    {
        $admin = AdminModel::first();

        $response = $this->actingAs($admin, 'admin')->get('/secretary/queue');
        $response->assertStatus(200);
        $response->assertSee('financial_summary_card', false);
        $response->assertSee('Daily Income', false);
        $response->assertSee('sec_allergy_alert_bar', false);
        $response->assertSee('patient_permanent_medhistory_tab_btn', false);
        $response->assertSee('save_sec_medhistory_btn', false);
    }
}
