<?php

namespace Tests\Feature;

use App\Models\ConsultationModel;
use App\Models\DoctorModel;
use App\Models\DoctorsProfileModel;
use App\Models\SecretaryModel;
use App\Models\SettlementsModel;
use App\Models\Stocks\StocksLedgerModel;
use App\Models\KayakapProfileModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Feature test suite for the end-to-end Outpatient Department (OPD) Consultation Workflow.
 * Validates doctor clinical diagnosis & admission orders, status transition to FOR_BILLING,
 * secretary settlement persistence with PHIC/HMO deductions, charge deletion,
 * and 1-click document printing (Rx, Instructions, Diagnostics, Admission, SOA).
 */
class OpdConsultationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected DoctorModel $doctor;
    protected SecretaryModel $secretary;
    protected DoctorsProfileModel $docProfile;
    protected ConsultationModel $consultation;

    /**
     * Detailed Comment: Seed database, establish doctor and secretary test users,
     * and initialize a sample walk-in consultation record.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);

        $this->doctor = DoctorModel::where('username', 'doctor')->firstOrFail();
        $this->secretary = SecretaryModel::where('username', 'secretary')->firstOrFail();

        // Create matching doctor profile
        $this->docProfile = DoctorsProfileModel::create([
            'docrefno' => $this->doctor->docrefno,
            'docname' => 'Dr. John Doe, MD',
            'Licno' => 'LIC123456',
            'PTR' => 'PTR987654',
            'S2no' => 'S2-112233',
        ]);

        // Create clinic facility profile for print headers
        KayakapProfileModel::create([
            'HOSP_NAME' => 'KayakapMD Central Clinic',
            'HOSP_ADDBRGY' => 'Poblacion, Makati City',
            'DATE_REGISTERED' => now()->format('Y-m-d'),
        ]);

        // Create sample consultation record
        $this->consultation = ConsultationModel::create([
            'consultationrefno' => 'CNSL-TEST-001',
            'pincode' => 'PIN-1001',
            'pxrefno' => 'PXREF-1001',
            'patientname' => 'Juan Dela Cruz',
            'age' => '35',
            'gender' => 'Male',
            'birthday' => '1991-05-15',
            'address' => 'Unit 101, Test St, Makati City',
            'mobilenumber' => '09171234567',
            'docrefno' => $this->doctor->docrefno,
            'docname' => 'Dr. John Doe, MD',
            'status' => 'IN_CONSULTATION',
            'reasonforconsultation' => 'Severe lower right quadrant abdominal pain and fever',
        ]);
    }

    /**
     * Detailed Comment: Verify doctor can save impressions, diagnosis, instructions,
     * and admission orders (foradmit toggle + instructions for kin/watcher).
     */
    public function test_doctor_saves_impressions_diagnosis_and_admission_orders(): void
    {
        $response = $this->actingAs($this->doctor, 'doctor')
            ->postJson('/api/save_impressions_diagnosis', [
                'consultationrefno' => $this->consultation->consultationrefno,
                'impression' => 'Acute Appendicitis with impending perforation',
                'diagnosis' => 'K35.80 - Unspecified acute appendicitis',
                'instructions' => 'Strict NPO (nothing by mouth). Start IV hydration.',
                'foradmit' => 1,
                'foradmit_instructions' => 'Direct admission to Surgical Ward. Please escort watcher to Admitting/Triage immediately.',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('pxwalkinconsultation', [
            'consultationrefno' => $this->consultation->consultationrefno,
            'impression' => 'Acute Appendicitis with impending perforation',
            'finadiagnosis' => 'K35.80 - Unspecified acute appendicitis',
            'foradmit' => 1,
            'foradmit_instructions' => 'Direct admission to Surgical Ward. Please escort watcher to Admitting/Triage immediately.',
        ]);
    }

    /**
     * Detailed Comment: Verify that completing a consultation transitions its queue status
     * to FOR_BILLING, handing off the record from the doctor back to the secretary queue.
     */
    public function test_doctor_completes_consultation_transitions_queue_status_to_for_billing(): void
    {
        $response = $this->actingAs($this->doctor, 'doctor')
            ->postJson('/api/complete_consultation', [
                'consultationrefno' => $this->consultation->consultationrefno,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('pxwalkinconsultation', [
            'consultationrefno' => $this->consultation->consultationrefno,
            'status' => 'FOR_BILLING',
        ]);
    }

    /**
     * Detailed Comment: Verify secretary can save settlements with PHIC deduction,
     * HMO coverage, and cash payment, generating an audit transaction reference and net payable balance.
     */
    public function test_secretary_saves_settlement_with_phic_and_hmo_deductions(): void
    {
        // Add itemized charges in stocks_ledger
        StocksLedgerModel::create([
            'transactiontype' => 'CHARGES',
            'px_pin' => $this->consultation->pxrefno,
            'px_consultcode_cn' => $this->consultation->consultationrefno,
            'patient_name' => $this->consultation->patientname,
            'prodcode' => 'MED-001',
            'item_dscr' => 'Paracetamol 500mg Tab',
            'qty' => 10,
            'cost_ave' => 10.00,
            'retails' => 10.00,
            'totalamt' => 100.00,
            'item_grouping' => 'DRUGS AND MEDS',
        ]);

        StocksLedgerModel::create([
            'transactiontype' => 'CHARGES',
            'px_pin' => $this->consultation->pxrefno,
            'px_consultcode_cn' => $this->consultation->consultationrefno,
            'patient_name' => $this->consultation->patientname,
            'prodcode' => 'PF-001',
            'item_dscr' => 'General Specialist Professional Fee',
            'qty' => 1,
            'cost_ave' => 1500.00,
            'retails' => 1500.00,
            'totalamt' => 1500.00,
            'item_grouping' => 'PROFESSIONAL FEE',
        ]);

        // Total gross charges = 1,600.00
        // PHIC = 300.00, HMO = 800.00, Net Payable = 500.00, Cash = 500.00
        $response = $this->actingAs($this->secretary, 'secretary')
            ->postJson('/api/save_settlements', [
                'sett_consultationrefno' => $this->consultation->consultationrefno,
                'total' => 1600.00,
                'phic' => 300.00,
                'hmo' => 800.00,
                'hmo_type' => 'Maxicare',
                'cash' => 500.00,
                'cta' => 0.00,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $settlement = SettlementsModel::where('consultationrefno', $this->consultation->consultationrefno)->first();
        $this->assertNotNull($settlement);
        $this->assertEquals(1600.00, (float) $settlement->total_gross);
        $this->assertEquals(300.00, (float) $settlement->less_phic);
        $this->assertEquals(800.00, (float) $settlement->less_hmo);
        $this->assertEquals(500.00, (float) $settlement->net_payable);
        $this->assertEquals(500.00, (float) $settlement->payment_cash);
        $this->assertEquals(100.00, (float) $settlement->total_meds);
        $this->assertEquals(1500.00, (float) $settlement->total_doctorspf);
        $this->assertStringStartsWith('TRX', $settlement->transactionrefno);
    }

    /**
     * Detailed Comment: Verify secretary can remove a charge item from stocks_ledger
     * via the corrected /api/delete_patient_charge endpoint.
     */
    public function test_secretary_removes_charge_via_delete_patient_charge(): void
    {
        $charge = StocksLedgerModel::create([
            'transactiontype' => 'CHARGES',
            'px_pin' => $this->consultation->pxrefno,
            'px_consultcode_cn' => $this->consultation->consultationrefno,
            'patient_name' => $this->consultation->patientname,
            'prodcode' => 'DIAG-CBC-01',
            'item_dscr' => 'Complete Blood Count (CBC)',
            'qty' => 1,
            'cost_ave' => 250.00,
            'retails' => 250.00,
            'totalamt' => 250.00,
            'item_grouping' => 'DIAGNOSTIC',
        ]);

        $this->assertDatabaseHas('stocks_ledger', [
            'px_consultcode_cn' => $this->consultation->consultationrefno,
            'prodcode' => 'DIAG-CBC-01',
        ]);

        $response = $this->actingAs($this->secretary, 'secretary')
            ->postJson('/api/delete_patient_charge', [
                'consultationrefno' => $this->consultation->consultationrefno,
                'prodcode' => 'DIAG-CBC-01',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('stocks_ledger', [
            'px_consultcode_cn' => $this->consultation->consultationrefno,
            'prodcode' => 'DIAG-CBC-01',
        ]);
    }

    /**
     * Detailed Comment: Verify printable PDF document generation across all 5 operational document types:
     * Rx, Instructions, Diagnostics, Admission Orders, and Statement of Account (SOA).
     */
    public function test_pdf_printable_endpoints_stream_successfully(): void
    {
        // Populate sample admission orders and settlement details
        $this->consultation->update([
            'foradmit' => 1,
            'foradmit_instructions' => 'Admit to medical ward under Dr. Doe.',
            'instructions' => 'Follow up in 3 days.',
            'impression' => 'Acute Gastroenteritis',
        ]);

        SettlementsModel::create([
            'transactionrefno' => 'TRX09202026120000',
            'consultationrefno' => $this->consultation->consultationrefno,
            'total_gross' => 500.00,
            'net_payable' => 300.00,
            'less_phic' => 200.00,
            'payment_cash' => 300.00,
            'createdby' => 'Secretary Doe',
        ]);

        $types = ['rx', 'instructions', 'diagnostics', 'admission', 'soa'];

        foreach ($types as $docType) {
            $response = $this->actingAs($this->secretary, 'secretary')
                ->get("/print_pdf?type={$docType}&consultationrefno={$this->consultation->consultationrefno}");

            $response->assertStatus(200);
            $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        }
    }
}
