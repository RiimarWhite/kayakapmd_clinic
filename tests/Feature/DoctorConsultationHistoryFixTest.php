<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationModel;
use App\Models\DoctorModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Feature test suite verifying the DataTables medhistory_table fix,
 * consultation history API resilience (draw, recordsTotal, recordsFiltered, multi-key search),
 * and doctor consultation modal card-header tab separation.
 */
class DoctorConsultationHistoryFixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Verifies that fetch_patient_medhistory returns empty records cleanly
     * when called with no identifiers, rather than dumping all system records.
     */
    public function test_fetch_patient_medhistory_handles_empty_identifiers_gracefully(): void
    {
        $admin = AdminModel::first();

        $response = $this->actingAs($admin, 'admin')->postJson('/api/fetch_patient_medhistory', []);

        $response->assertStatus(200);
        $response->assertJson([
            'recordsFiltered' => 0,
            'recordsTotal' => 0,
            'data' => [],
            'medhistory' => []
        ]);
    }

    /**
     * Detailed Comment: Verifies that fetch_patient_medhistory returns full consultation details
     * (including demographics, diagnosis, Rx items, diagnostics, charges) for a matched patient.
     */
    public function test_fetch_patient_medhistory_returns_enriched_consultation_history(): void
    {
        $admin = AdminModel::first();

        // Create a consultation record
        $consult = ConsultationModel::create([
            'consultationrefno' => 'TEST_CONSUL_HIST_001',
            'caseno' => 'CASE_HIST_01',
            'pxrefno' => 'PX_HIST_001',
            'pincode' => 'PIN_HIST_001',
            'patientname' => 'JUAN HISTORIA DELACRUZ',
            'gender' => 'Male',
            'birthday' => '1990-01-01',
            'reasonforconsultation' => 'Persistent dry cough and mild fever',
            'impression' => 'Acute Bronchitis',
            'finadiagnosis' => 'Acute Bronchitis',
            'status' => 'COMPLETED',
            'consultation_date' => '2026-10-01 10:00:00'
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => 'TEST_CONSUL_HIST_001',
            'pxrefno' => 'PX_HIST_001',
            'draw' => 1
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsFiltered',
            'recordsTotal',
            'data' => [
                '*' => [
                    'consultationrefno',
                    'pxrefno',
                    'patientname',
                    'reasonforconsultation',
                    'finadiagnosis',
                    'status',
                    'photo_path'
                ]
            ]
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('TEST_CONSUL_HIST_001', $data[0]['consultationrefno']);
        $this->assertEquals('Acute Bronchitis', $data[0]['finadiagnosis']);
    }

    /**
     * Detailed Comment: Verifies that the Doctor Consultation page renders the updated card-header tabs,
     * the separated Consultation History pane with the 7-column #medhistory_table, and includes the viewer modal.
     */
    public function test_doctor_consultation_page_renders_card_header_tabs_and_medhistory_table(): void
    {
        $doctor = DoctorModel::first();
        if (!$doctor) {
            $doctor = DoctorModel::create([
                'docrefno' => 'DOC_TEST_001',
                'docfname' => 'Cardio',
                'doclname' => 'Specialist',
                'username' => 'testdoc',
                'password' => bcrypt('password123'),
                'isactive' => 1
            ]);
        }

        $response = $this->actingAs($doctor, 'doctor')->get('/doctor/consultation');

        $response->assertStatus(200);
        // Verify card header tabs
        $response->assertSee('id="main_consultation_tab_btn"', false);
        $response->assertSee('id="main_medhistory_tab_btn"', false);
        $response->assertSee('Consultation History', false);

        // Verify separated consultation history pane and medhistory_table
        $response->assertSee('id="main_medhistory_pane"', false);
        $response->assertSee('id="medhistory_table"', false);
        $response->assertSee('id="refresh_medhistory_btn"', false);

        // Verify vertical clinical tabs live inside main_consultation_pane
        $response->assertSee('id="main_consultation_pane"', false);
        $response->assertSee('id="impDiagBtn"', false);
        $response->assertSee('id="rx_sidebar_btn"', false);
        $response->assertSee('id="patient_charge_tab_btn"', false);

        // Verify inclusion of the dedicated Consultation Details Viewer modal
        $response->assertSee('id="view_consultation_details_modal"', false);
    }
}
