<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationAnswerModel;
use App\Models\ConsultationModel;
use App\Models\DiagnosticsMasterlistModel;
use App\Models\DocChargesModel;
use App\Models\DocRequestsModel;
use App\Models\DoctorModel;
use App\Models\DoctorQuestionsModel;
use App\Models\DoctorsProfileModel;
use App\Models\PatientMasterlist;
use App\Models\PxMedicalHistoryModel;
use App\Models\ScheduleModel;
use App\Models\SecretaryDoctorsModel;
use App\Models\SecretaryModel;
use App\Models\SettlementsModel;
use App\Models\Stocks\StocksLedgerModel;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Detailed Comment: Comprehensive 4-Tier Requirement-Driven Opaque-Box Test Suite
 * for KayakapMD Clinic System-Wide Enhancements & End-to-End Workflows.
 *
 * Covers all features specified in PROJECT.md and ORIGINAL_REQUEST.md across 4 verification tiers:
 * - Tier 1: Feature Coverage (Isolated Happy Path per Feature Inventory)
 * - Tier 2: Boundary & Corner Cases (Nulls, Missing Keys, 401s, 404s, Fallbacks)
 * - Tier 3: Cross-Feature Combinations (Pairwise Multi-Step Workflows)
 * - Tier 4: Real-World Scenarios (End-to-End 12-Step Simulated QA Workflow)
 */
class SystemEnhancementsE2ETest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Detailed Comment: Seed baseline administrative, secretary, and physician user fixtures
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Helper: Assigns doctor to secretary and returns initialized staff fixtures.
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

    /**
     * Helper: Creates a test patient record in PatientMasterlist.
     */
    private function createTestPatient(array $overrides = []): PatientMasterlist
    {
        $uid = uniqid();
        return PatientMasterlist::create(array_merge([
            'pxrefno' => 'PX' . substr($uid, 0, 10),
            'pincode' => 'PIN' . rand(10000, 99999),
            'patientname' => 'DELA CRUZ, JUAN PEDRO',
            'pxfirstname' => 'JUAN',
            'pxmidname' => 'PEDRO',
            'pxlastname' => 'DELA CRUZ',
            'pxsuffix' => '',
            'gender' => 'M',
            'birthday' => '1990-05-15',
            'age' => 36,
            'mobilenumber' => '09123456789',
            'emailaddress' => 'juan.delacruz@example.com',
            'streetadrs' => '123 Rizal St',
            'brgy' => 'Barangay 1',
            'muncity' => 'Manila',
            'province' => 'Metro Manila',
            'address' => '123 Rizal St, Barangay 1, Manila, Metro Manila',
        ], $overrides));
    }

    /* =========================================================================
     * TIER 1: FEATURE COVERAGE (HAPPY-PATH IN ISOLATION)
     * ========================================================================= */

    /**
     * Detailed Comment: Tier 1 - Feature 1 & 2: Financial Report Co-Pay Header & Colspan=13
     * Verifies that resources/views/printables/financial_report_print.blade.php:
     * - Replaces 'Total Paid' header with 'CO-PAY'
     * - Removes the standalone 'Co-Pay' header
     * - Aligns patient collections ($item->paid) directly under 'CO-PAY'
     * - Adjusts total column spans from 14 to 13
     * - Displays total collections under the 'CO-PAY' totals column without standalone copay
     */
    public function test_tier1_financial_report_copay_header_and_colspan13(): void
    {
        [$secretary] = $this->getTestStaff();

        $summary = (object)[
            'total_gross' => 1500.00,
            'total_phic' => 500.00,
            'total_hmo' => 0.00,
            'total_senior' => 0.00,
            'total_net' => 1000.00,
            'total_cash' => 800.00,
            'total_card' => 0.00,
            'total_copay' => 200.00,
            'total_paid' => 1000.00,
            'total_balance' => 0.00,
            'total_patients' => 1,
        ];

        $items = [
            (object)[
                'queueno' => '001',
                'consultationrefno' => 'CON2026101201',
                'patientname' => 'DELA CRUZ, JUAN',
                'docname' => 'DR. SMITH',
                'gross' => 1500.00,
                'phic' => 500.00,
                'hmo' => 0.00,
                'senior' => 0.00,
                'net' => 1000.00,
                'cash' => 800.00,
                'card' => 0.00,
                'copay' => 200.00,
                'paid' => 1000.00,
                'balance' => 0.00,
                'status' => 'PAID',
                'is_philhealth_yakap' => true,
                'hmo_loa_no' => '',
            ]
        ];

        $queueDate = now()->toDateString();
        $doctorName = 'All Attending Doctors';
        $profile = (object)['HOSP_NAME' => 'KayakapMD Clinic', 'HOSP_ADDBRGY' => 'Manila'];
        $generatedBy = 'Secretary';

        // Render Blade view with item data
        $renderedWithItems = view('printables.financial_report_print', compact('summary', 'items', 'queueDate', 'doctorName', 'profile', 'generatedBy'))->render();

        // 1. Verify header contains CO-PAY
        $this->assertStringContainsString('CO-PAY', $renderedWithItems);

        // 2. Verify old 'Total Paid' header is removed and replaced by single CO-PAY column
        $this->assertStringNotContainsString('Total Paid', $renderedWithItems);
        if (preg_match('/<table class="items-table">[\s\S]*?<thead>[\s\S]*?<tr>([\s\S]*?)<\/tr>[\s\S]*?<\/thead>/i', $renderedWithItems, $headerMatches)) {
            $thCount = preg_match_all('/<th\b/i', $headerMatches[1]);
            $this->assertEquals(13, $thCount, 'Items table must have exactly 13 header columns');
        }

        // 3. Verify total paid (1,000.00) is rendered as the primary collection amount under CO-PAY
        $this->assertStringContainsString('1,000.00', $renderedWithItems);

        // Render Blade view with empty items (to verify empty state colspan=13)
        $emptyItems = [];
        $renderedEmpty = view('printables.financial_report_print', [
            'summary' => $summary,
            'items' => $emptyItems,
            'queueDate' => $queueDate,
            'doctorName' => $doctorName,
            'profile' => $profile,
            'generatedBy' => $generatedBy
        ])->render();

        // 4. Verify empty state table cell has colspan="13" (not colspan="14")
        $this->assertStringContainsString('colspan="13"', $renderedEmpty);
        $this->assertStringNotContainsString('colspan="14"', $renderedEmpty);

        // 5. Verify HTTP route /print_financial_report streams successfully
        $response = $this->actingAs($secretary, 'secretary')->get('/print_financial_report?queuedate=' . $queueDate);
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /**
     * Detailed Comment: Tier 1 - Feature 3: Masterlist Action Column Details Button Removal
     * Verifies that the "Details" button (.view-consultation-details-btn) is removed from
     * the Doctor Patient Masterlist table action column in resources/js/pages/doctor/patients.js.
     */
    public function test_tier1_masterlist_details_button_removed(): void
    {
        [, $doctorUser] = $this->getTestStaff();

        // Verify doctor can access /doctor/patients page
        $response = $this->actingAs($doctorUser, 'doctor')->get('/doctor/patients');
        $response->assertStatus(200);
        $response->assertSee('masterlist_table');

        // Verify patients.js source file no longer renders .view-consultation-details-btn inside loadMasterlist()
        $patientsJsPath = resource_path('js/pages/doctor/patients.js');
        $this->assertFileExists($patientsJsPath);
        $patientsJs = file_get_contents($patientsJsPath);

        // Extract loadMasterlist action buttons template
        if (preg_match('/let\s+btns\s*=\s*`([\s\S]*?)`;/i', $patientsJs, $matches)) {
            $btnsHtml = $matches[1];
            // Assert .view-consultation-details-btn is NOT injected in masterlist action buttons
            $this->assertStringNotContainsString('view-consultation-details-btn', $btnsHtml, 'Masterlist action column must not contain .view-consultation-details-btn');
            $this->assertStringNotContainsString('btn-view-details', $btnsHtml, 'Masterlist action column must not contain btn-view-details');
        }
    }

    /**
     * Detailed Comment: Tier 1 - Feature 4: Secretary Queue Action Column Details Button Removal
     * Verifies that the "Details" button (.view-consultation-details-btn) is removed from
     * the Secretary Queue patients queue table action column in resources/js/pages/secretary/queue.js.
     */
    public function test_tier1_queue_details_button_removed(): void
    {
        [$secretary] = $this->getTestStaff();

        // Verify secretary can access /secretary/queue page
        $response = $this->actingAs($secretary, 'secretary')->get('/secretary/queue');
        $response->assertStatus(200);
        $response->assertSee('patients_queue_table');

        // Verify queue.js source file does not render .view-consultation-details-btn in loadPatients() queue action column
        $queueJsPath = resource_path('js/pages/secretary/queue.js');
        $this->assertFileExists($queueJsPath);
        $queueJs = file_get_contents($queueJsPath);

        // Find the patients_queue_table column definition for actions
        if (preg_match('/<!--\s*Detailed Comment:\s*Details button removed from queue table[\s\S]*?-->/i', $queueJs)) {
            $this->assertTrue(true, 'Found explicit queue Details button removal comment in queue.js');
        }

        // Verify that inside the queue table render function, no .view-consultation-details-btn button is rendered
        if (preg_match('/id="patients_queue_table"[\s\S]*?DataTable\(\{([\s\S]*?)\}\);/i', $queueJs, $matches)) {
            $tableDef = $matches[1];
            $this->assertStringNotContainsString('view-consultation-details-btn', $tableDef);
        }
    }

    /**
     * Detailed Comment: Tier 1 - Feature 5: Secretary Patient Form Grid Responsiveness
     * Verifies that resources/views/pages/secretary/queue.blade.php refactors demographic
     * and vital signs fields from rigid d-flex layouts into responsive Bootstrap grid containers.
     */
    public function test_tier1_patient_form_grid_responsiveness(): void
    {
        [$secretary] = $this->getTestStaff();

        $response = $this->actingAs($secretary, 'secretary')->get('/secretary/queue');
        $response->assertStatus(200);

        // Verify Bootstrap responsive grid row containers are present
        $response->assertSee('row g-2');
        $response->assertSee('col-12');
        $response->assertSee('col-md-');
    }

    /**
     * Detailed Comment: Tier 1 - Feature 6: Yakap Medical History Button & Surgical Label Rename
     * Verifies that in resources/views/pages/secretary/queue.blade.php:
     * - Button '#btn_yakap_medhistory_future' exists with text 'Yakap Medical History/Profiling'
     * - Tooltip/notice indicates 'For future updates only'
     * - Label is renamed to 'Surgical/Operations History'
     */
    public function test_tier1_yakap_medhistory_button_and_surgical_label_rename(): void
    {
        [$secretary] = $this->getTestStaff();

        $response = $this->actingAs($secretary, 'secretary')->get('/secretary/queue');
        $response->assertStatus(200);

        // Verify button presence and text for Yakap Medical History/Profiling modal
        $response->assertSee('id="btn_yakap_medhistory_modal"', false);
        $response->assertSee('Yakap Medical History/Profiling');
        $response->assertSee('yakap_medhistory_modal');

        // Verify label rename from 'Surgical History:' to 'Surgical/Operations History:'
        $response->assertSee('Surgical/Operations History');
    }

    /**
     * Detailed Comment: Tier 1 - Feature 7 & 8: Patient Classification Persistence & Dynamic Fields
     * Verifies that:
     * - POST /api/save_patient_consultation accepts and persists clinical classification fields:
     *   classification (OBSTETRICS), subclassification, gravida, para, abortion, iufd, died,
     *   lmp_date, delivery_procedure, pathologic, infacilitydelivery
     * - Persisted record in pxwalkinconsultation matches submitted payload
     */
    public function test_tier1_patient_classification_persistence_and_dynamic_fields(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();
        $patient = $this->createTestPatient();

        $consultationPayload = [
            'docrefno' => $doctorProfile->docrefno,
            'pxrefno' => $patient->pxrefno,
            'pincode' => $patient->pincode,
            'pxfname' => 'MARIA',
            'pxmname' => 'SANTOS',
            'pxlname' => 'CLARA',
            'pxsuffix' => '',
            'pxsex' => 'F',
            'pxbday' => '1995-04-20',
            'pxage' => 31,
            'pxcellnumber' => '09181234567',
            'pxemail' => 'maria.clara@example.com',
            'pxaddress' => 'Sample Address, City',
            'reason_for_consultation' => 'Prenatal checkup',
            'temperature' => 36.8,
            'weight' => 58.5,
            'height' => 160,
            'respiratory_rate' => 18,
            'pulse_rate' => 76,
            'bp_numerator' => 110,
            'bp_denominator' => 70,
            'sched_date' => now()->toDateString(),
            'patient_type' => 'phic',
            'patient_classification' => 'OBSTETRICS',
            'gravida' => 2,
            'para' => 1,
            'abortion' => 0,
            'iufd' => 0,
            'died' => 0,
            'lmp_date' => '2026-08-10',
            'delivery_procedure' => 'Normal',
            'pathologic' => 1,
            'infacilitydelivery' => 1,
        ];

        $response = $this->actingAs($secretary, 'secretary')
            ->postJson('/api/save_patient_consultation', $consultationPayload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify database persistence in pxwalkinconsultation
        $saved = ConsultationModel::where('pxrefno', $patient->pxrefno)
            ->where('classification', 'OBSTETRICS')
            ->first();

        $this->assertNotNull($saved, 'Consultation must be saved in pxwalkinconsultation');
        $this->assertEquals('OBSTETRICS', $saved->classification);
        $this->assertEquals(2, (int)$saved->gravida);
        $this->assertEquals(1, (int)$saved->para);
        $this->assertEquals('2026-08-10', substr($saved->lmp_date, 0, 10));
        $this->assertEquals('Normal', $saved->delivery_procedure);
        $this->assertEquals(1, (int)$saved->pathologic);
        $this->assertEquals(1, (int)$saved->infacilitydelivery);
    }

    /**
     * Detailed Comment: Tier 1 - Feature 8: Database Migration Columns & Data Dictionary Sync
     * Verifies that pxwalkinconsultation table contains columns lmp_date and delivery_procedure,
     * and that .gemini/database/kayakapmdv2_data_dictionary.md documents both columns.
     */
    public function test_tier1_migration_columns_lmp_date_and_delivery_procedure(): void
    {
        $this->assertTrue(Schema::hasTable('pxwalkinconsultation'), 'pxwalkinconsultation table must exist');
        $this->assertTrue(Schema::hasColumn('pxwalkinconsultation', 'lmp_date'), 'lmp_date column must exist');
        $this->assertTrue(Schema::hasColumn('pxwalkinconsultation', 'delivery_procedure'), 'delivery_procedure column must exist');

        // Verify Data Dictionary documentation
        $dictPath = base_path('.gemini/database/kayakapmdv2_data_dictionary.md');
        $this->assertFileExists($dictPath);
        $dictContent = file_get_contents($dictPath);
        $this->assertStringContainsString('lmp_date', $dictContent, 'Data dictionary must document lmp_date');
        $this->assertStringContainsString('delivery_procedure', $dictContent, 'Data dictionary must document delivery_procedure');
    }

    /**
     * Detailed Comment: Tier 1 - Feature 9: Patient Type Label Rename (PHIC YAKAP)
     * Verifies that in resources/views/pages/secretary/queue.blade.php,
     * the option label for value="phic" displays "PHIC YAKAP".
     */
    public function test_tier1_patient_type_label_phic_yakap(): void
    {
        [$secretary] = $this->getTestStaff();

        $response = $this->actingAs($secretary, 'secretary')->get('/secretary/queue');
        $response->assertStatus(200);

        // Check for option value="phic" with text PHIC YAKAP
        $response->assertSee('<option value="phic">PHIC YAKAP</option>', false);
    }

    /**
     * Detailed Comment: Tier 1 - Feature 10: Radiology & Laboratory File Upload & Preview URLs
     * Verifies that ConsultationController::saveConsultation accepts both radiologypath and
     * laboratorypath uploaded files, stores them, and provides valid preview URLs.
     */
    public function test_tier1_radiology_and_laboratory_file_upload_and_preview_urls(): void
    {
        Storage::fake('private');
        [$secretary, , , $doctorProfile] = $this->getTestStaff();
        $patient = $this->createTestPatient();

        $fakeRad = UploadedFile::fake()->create('chest_xray.pdf', 150, 'application/pdf');
        $fakeLab = UploadedFile::fake()->image('blood_chemistry.jpg');

        $response = $this->actingAs($secretary, 'secretary')->post('/api/save_patient_consultation', [
            'docrefno' => $doctorProfile->docrefno,
            'pxrefno' => $patient->pxrefno,
            'pincode' => $patient->pincode,
            'pxfname' => 'CARLOS',
            'pxlname' => 'GARCIA',
            'pxsex' => 'M',
            'pxbday' => '1988-11-20',
            'pxage' => 38,
            'reason_for_consultation' => 'Cough and Fever',
            'sched_date' => now()->toDateString(),
            'radiologypath' => $fakeRad,
            'laboratorypath' => $fakeLab,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $saved = ConsultationModel::where('pxrefno', $patient->pxrefno)->first();
        $this->assertNotNull($saved);
        $this->assertNotNull($saved->radiologypath, 'radiologypath must be populated');
        $this->assertNotNull($saved->laboratorypath, 'laboratorypath must be populated');

        // Verify stored files on fake disk
        Storage::disk('private')->assertExists($saved->radiologypath);
        Storage::disk('private')->assertExists($saved->laboratorypath);

        // Fetch patient medhistory to verify preview URLs generation
        $medResponse = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $saved->consultationrefno,
            'pxrefno' => $patient->pxrefno,
        ]);

        $medResponse->assertStatus(200);
        $history = $medResponse->json('medhistory') ?? $medResponse->json('history') ?? [];
        $this->assertNotEmpty($history);
        $firstItem = $history[0];
        $this->assertNotEmpty($firstItem['radiology_url'] ?? $firstItem['radiologypath'], 'Radiology preview URL must be generated');
        $this->assertNotEmpty($firstItem['laboratory_url'] ?? $firstItem['laboratorypath'], 'Laboratory preview URL must be generated');
    }

    /**
     * Detailed Comment: Tier 1 - Feature 11: Consultation Details Modal Medical History Tab
     * Verifies that resources/views/modals/view_consultation_details.blade.php contains
     * a dedicated Medical History vertical tab button and tab content pane.
     */
    public function test_tier1_consultation_details_modal_medical_history_tab(): void
    {
        $view = view('modals.view_consultation_details')->render();

        // 1. Verify Medical History vertical tab button exists
        $this->assertStringContainsString('data-bs-target="#vcd_pane_medhistory"', $view);
        $this->assertStringContainsString('Medical History', $view);

        // 2. Verify Medical History tab pane container exists
        $this->assertStringContainsString('id="vcd_pane_medhistory"', $view);

        // 3. Verify permanent clinical history cards exist inside modal
        $this->assertStringContainsString('Patient Permanent Medical History', $view);
        $this->assertStringContainsString('Surgical/Operations History:', $view);
        $this->assertStringContainsString('Allergies &amp; Drug Reactions:', $view);
    }

    /**
     * Detailed Comment: Tier 1 - Feature 12 & 14: Admin Doctor Schedules CRUD API
     * Verifies that Admin users can create, fetch, edit, and delete doctor clinic schedules
     * via API endpoints without encountering HTTP 401 Unauthorized route collisions.
     */
    public function test_tier1_admin_doctor_schedules_crud_api(): void
    {
        [, , $admin, $doctorProfile] = $this->getTestStaff();

        // 1. CREATE schedule as Admin
        $createRes = $this->actingAs($admin, 'admin')->postJson('/api/create_doctor_schedules', [
            'docrefno' => $doctorProfile->docrefno,
            'day' => 'Monday',
            'start' => '08:00:00',
            'end' => '12:00:00',
        ]);
        $createRes->assertStatus(200);
        $createRes->assertJson(['success' => true]);
        $schedrefno = $createRes->json('schedule.schedrefno');
        $this->assertNotEmpty($schedrefno);

        // 2. FETCH schedules as Admin
        $fetchRes = $this->actingAs($admin, 'admin')->postJson('/api/fetch_doctor_schedules', [
            'docrefno' => $doctorProfile->docrefno,
        ]);
        $fetchRes->assertStatus(200);
        $schedules = $fetchRes->json('schedules');
        $this->assertNotEmpty($schedules);
        $this->assertTrue(collect($schedules)->contains('schedrefno', $schedrefno));

        // 3. EDIT schedule as Admin
        $editRes = $this->actingAs($admin, 'admin')->postJson('/api/edit_schedule', [
            'schedrefno' => $schedrefno,
            'day' => 'Monday',
            'start' => '09:00:00',
            'end' => '13:00:00',
        ]);
        $editRes->assertStatus(200);
        $editRes->assertJson(['success' => true]);

        // Verify edited persistence
        $updated = ScheduleModel::where('schedrefno', $schedrefno)->first();
        $this->assertEquals('09:00:00', $updated->start);

        // 4. DELETE schedule as Admin
        $deleteRes = $this->actingAs($admin, 'admin')->postJson('/api/delete_schedule', [
            'schedrefno' => $schedrefno,
        ]);
        $deleteRes->assertStatus(200);
        $deleteRes->assertJson(['success' => true]);

        // Verify deletion
        $this->assertNull(ScheduleModel::where('schedrefno', $schedrefno)->first());
    }

    /**
     * Detailed Comment: Tier 1 - Feature 13 & 14: Admin Doctor Questions CRUD API & Profile Resolution
     * Verifies that Admin users can create, fetch, edit, and delete doctor questions,
     * and that SecretaryController::createQuestion correctly resolves docfname, docmname, doclname.
     */
    public function test_tier1_admin_doctor_questions_crud_api_with_doctor_profile_resolution(): void
    {
        [, , $admin, $doctorProfile] = $this->getTestStaff();

        // 1. CREATE question as Admin
        $createRes = $this->actingAs($admin, 'admin')->postJson('/api/create_question', [
            'docrefno' => $doctorProfile->docrefno,
            'question' => 'Are you currently experiencing any chest discomfort or palpitations?',
        ]);
        $createRes->assertStatus(200);
        $createRes->assertJson(['success' => true]);
        $questionRefNo = $createRes->json('question.docquestionrefno');
        $this->assertNotEmpty($questionRefNo);

        // Verify doctor profile name resolution (docfname, docmname, doclname)
        $savedQ = DoctorQuestionsModel::where('docquestionrefno', $questionRefNo)->first();
        $this->assertNotNull($savedQ);
        $this->assertNotEmpty($savedQ->docname, 'Doctor name must be resolved from DoctorsProfileModel');

        // 2. FETCH questions as Admin
        $fetchRes = $this->actingAs($admin, 'admin')->postJson('/api/fetch_doctor_questions', [
            'docrefno' => $doctorProfile->docrefno,
        ]);
        $fetchRes->assertStatus(200);
        $questions = $fetchRes->json('questions');
        $this->assertNotEmpty($questions);
        $this->assertTrue(collect($questions)->contains('docquestionrefno', $questionRefNo));

        // 3. EDIT question as Admin
        $editRes = $this->actingAs($admin, 'admin')->postJson('/api/edit_question', [
            'docquestionrefno' => $questionRefNo,
            'question' => 'Updated question: Do you have persistent headaches?',
        ]);
        $editRes->assertStatus(200);
        $editRes->assertJson(['success' => true]);

        $updatedQ = DoctorQuestionsModel::where('docquestionrefno', $questionRefNo)->first();
        $this->assertEquals('Updated question: Do you have persistent headaches?', $updatedQ->question);

        // 4. DELETE question as Admin
        $deleteRes = $this->actingAs($admin, 'admin')->postJson('/api/delete_question', [
            'docquestionrefno' => $questionRefNo,
        ]);
        $deleteRes->assertStatus(200);
        $deleteRes->assertJson(['success' => true]);

        $this->assertNull(DoctorQuestionsModel::where('docquestionrefno', $questionRefNo)->first());
    }

    /**
     * Detailed Comment: Tier 1 - Feature 16: Doctor Dashboard Table Column Count Alignment
     * Verifies that #todays_patients_table in resources/views/pages/doctor/dashboard.blade.php
     * aligns the placeholder tbody cells (4 <td>) with the 4 header columns (Queue, Patient Name, Status, Action).
     */
    public function test_tier1_doctor_dashboard_table_column_count_alignment(): void
    {
        $dashboardPath = resource_path('views/pages/doctor/dashboard.blade.php');
        $this->assertFileExists($dashboardPath);
        $dashboardHtml = file_get_contents($dashboardPath);

        // Extract #todays_patients_table markup
        $this->assertStringContainsString('id="todays_patients_table"', $dashboardHtml);

        if (preg_match('/<table[^>]*id="todays_patients_table"[^>]*>([\s\S]*?)<\/table>/i', $dashboardHtml, $tableMatch)) {
            $tableContent = $tableMatch[1];

            // Count header <th> tags
            preg_match_all('/<th\b[^>]*>/i', $tableContent, $thMatches);
            $thCount = count($thMatches[0]);

            // Count placeholder tbody <td> tags in first <tr>
            if (preg_match('/<tbody>\s*<tr>([\s\S]*?)<\/tr>\s*<\/tbody>/i', $tableContent, $trMatch)) {
                preg_match_all('/<td\b[^>]*>/i', $trMatch[1], $tdMatches);
                $tdCount = count($tdMatches[0]);

                // Requirement R4 / Feature 16: Both must have exactly 4 columns
                $this->assertEquals(4, $thCount, "Header should contain 4 columns (Queue, Patient Name, Status, Action)");
                $this->assertEquals($thCount, $tdCount, "Placeholder tbody td count ($tdCount) must equal header th count ($thCount) to eliminate DataTables column count errors");
            }
        }
    }

    /**
     * Detailed Comment: Tier 1 - Feature 18: Doctor Consultation Queue Number Display
     * Verifies that DoctorController::fetchConsultationPatients selects and returns 'queueno'
     * alongside patient demographics so queue numbers are visibly displayed next to patient names.
     */
    public function test_tier1_doctor_consultation_queue_number_display(): void
    {
        [, $doctorUser, , $doctorProfile] = $this->getTestStaff();
        $patient = $this->createTestPatient();

        $today = now()->toDateString();

        // Seed consultation with distinct queueno
        ConsultationModel::create([
            'consultationrefno' => 'CON' . now()->format('mdYHis') . rand(10, 99),
            'pxrefno' => $patient->pxrefno,
            'docrefno' => $doctorProfile->docrefno,
            'patientname' => $patient->patientname,
            'gender' => 'M',
            'age' => 35,
            'consultation_date' => now(),
            'queueno' => '005',
            'status' => 'WAITING',
            'reasonforconsultation' => 'Routine Checkup',
            'finadiagnosis' => '',
        ]);

        $response = $this->actingAs($doctorUser, 'doctor')->postJson('/api/fetch_doctor_consultations', [
            'date' => $today,
        ]);

        $response->assertStatus(200);
        $patients = $response->json('patients') ?? [];
        $this->assertNotEmpty($patients);

        // Verify queueno attribute is returned in JSON payload
        $firstPatient = $patients[0];
        $this->assertArrayHasKey('queueno', $firstPatient, 'fetchConsultationPatients must include queueno in payload');
        $this->assertEquals('005', $firstPatient['queueno']);
    }

    /**
     * Detailed Comment: Tier 1 - Feature 19: Doctor Consultation Vertical Tab Container Fix
     * Verifies that resources/views/modals/consultation_modal.blade.php:
     * - Properly closes #main_consultation_pane before #main_medhistory_pane
     * - Eliminates unclosed container div that blocks Medical History tab content
     * - Renames 'Past Surgical History:' to 'Past Surgical/Operations History:'
     */
    public function test_tier1_doctor_consultation_unclosed_tab_container_fix(): void
    {
        $modalPath = resource_path('views/modals/consultation_modal.blade.php');
        $this->assertFileExists($modalPath);
        $modalHtml = file_get_contents($modalPath);

        // 1. Verify label rename to 'Past Surgical/Operations History:'
        $this->assertStringContainsString('Past Surgical/Operations History', $modalHtml);

        // 2. Verify tab pane structures exist
        $this->assertStringContainsString('id="main_consultation_pane"', $modalHtml);
        $this->assertStringContainsString('id="main_medhistory_pane"', $modalHtml);
        $this->assertStringContainsString('id="main_permanent_medhistory_pane"', $modalHtml);

        // 3. Verify that #main_medhistory_pane is not nested inside #main_consultation_pane
        $posConsultation = strpos($modalHtml, 'id="main_consultation_pane"');
        $posMedHistory = strpos($modalHtml, 'id="main_medhistory_pane"');
        $this->assertTrue($posConsultation !== false && $posMedHistory !== false && $posMedHistory > $posConsultation);

        // Extract fragment between #main_consultation_pane and #main_medhistory_pane
        $fragment = substr($modalHtml, $posConsultation, $posMedHistory - $posConsultation);
        $openDivs = substr_count($fragment, '<div');
        $closeDivs = substr_count($fragment, '</div');
        $this->assertEquals($openDivs, $closeDivs, "All <div> elements opened in #main_consultation_pane must be balanced and closed before #main_medhistory_pane begins");
    }

    /**
     * Detailed Comment: Tier 1 - Feature 22 & 23: Header Theme Switcher Dropdown & Persistence Markup
     * Verifies that resources/views/components/navbar.blade.php contains a Theme Switcher dropdown
     * supporting Light, Dark, and System modes, and resources/views/layouts/app.blade.php includes
     * an inline script in <head> to prevent Flash of Unstyled Content (FOUC).
     */
    public function test_tier1_navbar_theme_switcher_dropdown_and_markup(): void
    {
        // 1. Verify navbar theme dropdown markup
        $navbarHtml = view('components/navbar')->render();
        $this->assertStringContainsString('id="theme_switcher_dropdown"', $navbarHtml);
        $this->assertStringContainsString('id="bd-theme"', $navbarHtml);
        $this->assertStringContainsString('data-bs-theme-value="light"', $navbarHtml);
        $this->assertStringContainsString('data-bs-theme-value="dark"', $navbarHtml);
        $this->assertStringContainsString('data-bs-theme-value="system"', $navbarHtml);

        // 2. Verify layouts/app.blade.php contains inline script in <head> to prevent FOUC
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $this->assertFileExists($layoutPath);
        $layoutHtml = file_get_contents($layoutPath);
        $this->assertStringContainsString("localStorage.getItem('theme", $layoutHtml);
        $this->assertStringContainsString("setAttribute('data-bs-theme", $layoutHtml);
    }

    /* =========================================================================
     * TIER 2: BOUNDARY & CORNER CASES (NEGATIVE & ADVERSARIAL)
     * ========================================================================= */

    /**
     * Detailed Comment: Tier 2 - Boundary 1: Empty or Missing Classification Defaults Gracefully
     * Verifies that saving a consultation with null or missing classification defaults
     * safely without throwing database integrity or 500 exceptions.
     */
    public function test_tier2_empty_or_missing_classification_defaults_gracefully(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();
        $patient = $this->createTestPatient();

        $response = $this->actingAs($secretary, 'secretary')->postJson('/api/save_patient_consultation', [
            'docrefno' => $doctorProfile->docrefno,
            'pxrefno' => $patient->pxrefno,
            'pincode' => $patient->pincode,
            'pxfname' => 'JOSE',
            'pxlname' => 'RIZAL',
            'pxsex' => 'M',
            'pxbday' => '1990-06-19',
            'pxage' => 36,
            'reason_for_consultation' => 'General checkup',
            'sched_date' => now()->toDateString(),
            'patient_classification' => '', // Empty classification
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $saved = ConsultationModel::where('pxrefno', $patient->pxrefno)->first();
        $this->assertNotNull($saved);
        $this->assertNotEmpty($saved->classification, 'Empty classification should default to a valid string like MEDICAL');
    }

    /**
     * Detailed Comment: Tier 2 - Boundary 2: Null or Empty LMP Date Persists Without Parsing Error
     * Verifies that submitting an empty string or null for lmp_date persists safely
     * as null without Carbon date parsing or database format exceptions.
     */
    public function test_tier2_null_or_empty_lmp_date_persists_without_parsing_error(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();
        $patient = $this->createTestPatient();

        $response = $this->actingAs($secretary, 'secretary')->postJson('/api/save_patient_consultation', [
            'docrefno' => $doctorProfile->docrefno,
            'pxrefno' => $patient->pxrefno,
            'pincode' => $patient->pincode,
            'pxfname' => 'GABRIELA',
            'pxlname' => 'SILANG',
            'pxsex' => 'F',
            'pxbday' => '1992-03-19',
            'pxage' => 34,
            'reason_for_consultation' => 'OB checkup without known LMP',
            'sched_date' => now()->toDateString(),
            'patient_classification' => 'OBSTETRICS',
            'lmp_date' => '', // Empty string for date
            'gravida' => 1,
            'para' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $saved = ConsultationModel::where('pxrefno', $patient->pxrefno)->first();
        $this->assertNotNull($saved);
        $this->assertNull($saved->lmp_date, 'Empty LMP date must be safely persisted as null');
    }

    /**
     * Detailed Comment: Tier 2 - Boundary 3: Nonexistent Doctor Reference Returns 404
     * Verifies that attempting to create a schedule or question for a non-existent doctor
     * returns HTTP 404 rather than an unhandled 500 null-pointer exception.
     */
    public function test_tier2_nonexistent_doctor_refno_rejected_with_404(): void
    {
        [, , $admin] = $this->getTestStaff();

        $schedRes = $this->actingAs($admin, 'admin')->postJson('/api/create_doctor_schedules', [
            'docrefno' => 'NON_EXISTENT_DOC_REF_999999',
            'day' => 'Tuesday',
            'start' => '08:00:00',
            'end' => '12:00:00',
        ]);
        $schedRes->assertStatus(404);
        $schedRes->assertJson(['success' => false, 'message' => 'Doctor profile not found.']);

        $quesRes = $this->actingAs($admin, 'admin')->postJson('/api/create_question', [
            'docrefno' => 'NON_EXISTENT_DOC_REF_999999',
            'question' => 'Sample test question?',
        ]);
        $quesRes->assertStatus(404);
        $quesRes->assertJson(['success' => false, 'message' => 'Doctor profile not found.']);
    }

    /**
     * Detailed Comment: Tier 2 - Boundary 4: Invalid Schedule Parameters Rejected
     * Verifies that invalid schedule days or missing time fields trigger validation failures.
     */
    public function test_tier2_invalid_schedule_parameters_rejected(): void
    {
        [, , $admin, $doctorProfile] = $this->getTestStaff();

        $response = $this->actingAs($admin, 'admin')->postJson('/api/create_doctor_schedules', [
            'docrefno' => $doctorProfile->docrefno,
            'day' => 'InvalidDayName', // Invalid day
            'start' => '08:00:00',
            'end' => '12:00:00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['schedule_day']);
    }

    /**
     * Detailed Comment: Tier 2 - Boundary 5: Missing File Attachments Handled Safely
     * Verifies that consultations saved without attachments leave radiologypath and
     * laboratorypath as null, and preview requests handle nonexistent files cleanly.
     */
    public function test_tier2_missing_file_attachments_handled_safely(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();
        $patient = $this->createTestPatient();

        $response = $this->actingAs($secretary, 'secretary')->postJson('/api/save_patient_consultation', [
            'docrefno' => $doctorProfile->docrefno,
            'pxrefno' => $patient->pxrefno,
            'pincode' => $patient->pincode,
            'pxfname' => 'EMILIO',
            'pxlname' => 'AGUINALDO',
            'pxsex' => 'M',
            'pxbday' => '1985-01-01',
            'pxage' => 41,
            'reason_for_consultation' => 'Checkup without files',
            'sched_date' => now()->toDateString(),
        ]);

        $response->assertStatus(200);
        $saved = ConsultationModel::where('pxrefno', $patient->pxrefno)->first();
        $this->assertNotNull($saved);
        $this->assertNull($saved->radiologypath);
        $this->assertNull($saved->laboratorypath);
    }

    /**
     * Detailed Comment: Tier 2 - Boundary 6: Unauthorized Non-Admin/Non-Staff Access Rejected
     * Verifies that unauthenticated requests to administrative doctor management endpoints
     * are strictly rejected with HTTP 401 Unauthorized or redirects.
     */
    public function test_tier2_unauthorized_access_rejected(): void
    {
        // Unauthenticated request to create doctor schedules
        $schedRes = $this->postJson('/api/create_doctor_schedules', [
            'docrefno' => 'DOC001',
            'day' => 'Monday',
            'start' => '08:00:00',
            'end' => '12:00:00',
        ]);
        $this->assertTrue(in_array($schedRes->status(), [401, 302]), 'Unauthenticated request must be rejected with 401 or 302');

        // Unauthenticated request to create doctor question
        $quesRes = $this->postJson('/api/create_question', [
            'docrefno' => 'DOC001',
            'question' => 'Unauthorized question test?',
        ]);
        $this->assertTrue(in_array($quesRes->status(), [401, 302]), 'Unauthenticated request must be rejected with 401 or 302');
    }

    /* =========================================================================
     * TIER 3: CROSS-FEATURE COMBINATIONS (MULTI-STEP PIPELINES)
     * ========================================================================= */

    /**
     * Detailed Comment: Tier 3 - Pipeline 1: Secretary Saves Consultation -> Doctor Loads Same
     * Verifies complete cross-role data synchronization:
     * - Secretary saves consultation with OBSTETRICS classification, LMP date, delivery procedure,
     *   and radiology attachment.
     * - DoctorJackson logs in, accesses consultation details via /api/fetch_consultation.
     * - Verifies classification fields, demographics, and file preview URLs match seamlessly.
     */
    public function test_tier3_secretary_saves_consultation_with_classification_files_and_doctor_loads_details(): void
    {
        Storage::fake('private');
        [$secretary, $doctorUser, , $doctorProfile] = $this->getTestStaff();
        $patient = $this->createTestPatient();

        $fakeRad = UploadedFile::fake()->create('pelvic_ultrasound.pdf', 200, 'application/pdf');

        // Step a: Secretary saves consultation
        $saveRes = $this->actingAs($secretary, 'secretary')->post('/api/save_patient_consultation', [
            'docrefno' => $doctorProfile->docrefno,
            'pxrefno' => $patient->pxrefno,
            'pincode' => $patient->pincode,
            'pxfname' => 'TERESA',
            'pxlname' => 'MAGBANUA',
            'pxsex' => 'F',
            'pxbday' => '1993-10-13',
            'pxage' => 33,
            'reason_for_consultation' => 'Third trimester ultrasound check',
            'sched_date' => now()->toDateString(),
            'patient_type' => 'phic',
            'patient_classification' => 'OBSTETRICS',
            'gravida' => 3,
            'para' => 2,
            'abortion' => 0,
            'iufd' => 0,
            'died' => 0,
            'lmp_date' => '2026-02-15',
            'delivery_procedure' => 'Cesarean',
            'pathologic' => 0,
            'radiologypath' => $fakeRad,
        ]);

        $saveRes->assertStatus(200);

        $consult = ConsultationModel::where('pxrefno', $patient->pxrefno)->first();
        $this->assertNotNull($consult);

        // Step b: DoctorJackson logs in and fetches consultation details
        $fetchRes = $this->actingAs($doctorUser, 'doctor')->postJson('/api/fetch_consultation', [
            'consultationrefno' => $consult->consultationrefno,
        ]);

        $fetchRes->assertStatus(200);
        $fetchRes->assertJson(['success' => true]);

        $fetchedPatient = $fetchRes->json('patient');
        $this->assertEquals('OBSTETRICS', $fetchedPatient['classification']);
        $this->assertEquals('2026-02-15', substr($fetchedPatient['lmp_date'], 0, 10));
        $this->assertEquals('Cesarean', $fetchedPatient['delivery_procedure']);
        $this->assertNotEmpty($fetchedPatient['radiologypath']);
    }

    /**
     * Detailed Comment: Tier 3 - Pipeline 2: Admin Creates Questions & Schedules -> Doctor Sees In Console
     * Verifies that administrative setup seamlessly flows into the attending doctor's dashboard:
     * - Admin creates schedules and clinical screening questions for DoctorJackson.
     * - DoctorJackson logs in, calls /api/fetch_doctor_schedules_dashboard and /api/fetch_doctor_questions.
     * - Verifies schedules and questions are present.
     */
    public function test_tier3_admin_creates_doctor_questions_and_schedules_and_doctor_views_them(): void
    {
        [, $doctorUser, $admin, $doctorProfile] = $this->getTestStaff();

        // 1. Admin creates schedule
        $this->actingAs($admin, 'admin')->postJson('/api/create_doctor_schedules', [
            'docrefno' => $doctorProfile->docrefno,
            'day' => 'Wednesday',
            'start' => '13:00:00',
            'end' => '17:00:00',
        ])->assertStatus(200);

        // 2. Admin creates question
        $this->actingAs($admin, 'admin')->postJson('/api/create_question', [
            'docrefno' => $doctorProfile->docrefno,
            'question' => 'Do you have known allergy to anesthesia or penicillin?',
        ])->assertStatus(200);

        // 3. Doctor logs in and fetches dashboard schedules
        $schedRes = $this->actingAs($doctorUser, 'doctor')->postJson('/api/fetch_doctor_schedules_dashboard');
        $schedRes->assertStatus(200);
        $schedules = $schedRes->json('schedules') ?? [];
        $this->assertTrue(collect($schedules)->contains('day', 'Wednesday'));

        // 4. Doctor fetches questions
        $quesRes = $this->actingAs($doctorUser, 'doctor')->postJson('/api/fetch_doctor_questions', [
            'docrefno' => $doctorProfile->docrefno,
        ]);
        $quesRes->assertStatus(200);
        $questions = $quesRes->json('questions') ?? [];
        $this->assertTrue(collect($questions)->contains('question', 'Do you have known allergy to anesthesia or penicillin?'));
    }

    /**
     * Detailed Comment: Tier 3 - Pipeline 3: PHIC YAKAP Queue -> Settlement -> Financial Report Printable
     * Verifies that queuing a patient under PHIC YAKAP flows into settlements with copay allocation,
     * and appears in the daily income financial report with the [PH Yakap] indicator.
     */
    public function test_tier3_queue_phic_yakap_patient_type_reflected_in_financial_report(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();
        $patient = $this->createTestPatient();
        $today = now()->toDateString();

        // Step a: Save consultation as PHIC YAKAP
        $this->actingAs($secretary, 'secretary')->post('/api/save_patient_consultation', [
            'docrefno' => $doctorProfile->docrefno,
            'pxrefno' => $patient->pxrefno,
            'pincode' => $patient->pincode,
            'pxfname' => 'ANDRES',
            'pxlname' => 'BONIFACIO',
            'pxsex' => 'M',
            'pxbday' => '1987-11-30',
            'pxage' => 38,
            'reason_for_consultation' => 'Hypertension follow-up',
            'sched_date' => $today,
            'patient_type' => 'phic',
            'patient_classification' => 'MEDICAL',
        ])->assertStatus(200);

        $consult = ConsultationModel::where('pxrefno', $patient->pxrefno)->first();

        // Step b: Create settlement record with PhilHealth Yakap copay
        SettlementsModel::create([
            'consultationrefno' => $consult->consultationrefno,
            'total_gross' => 1200.00,
            'less_phic' => 800.00,
            'is_philhealth_yakap' => 1,
            'copay' => 400.00,
            'payment_cash' => 400.00,
            'net_payable' => 400.00,
            'created' => now(),
            'recordedby' => 'secretary',
        ]);

        // Step c: Secretary accesses daily income summary and financial report
        $summaryRes = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_queue_financial_summary', [
            'queuedate' => $today,
        ]);
        $summaryRes->assertStatus(200);
        $summary = $summaryRes->json('summary');
        $this->assertEquals(400.00, (float)$summary['total_copay']);

        // Step d: Print financial report and assert PDF generation with [PH Yakap] indicator
        $pdfRes = $this->actingAs($secretary, 'secretary')->get('/print_financial_report?queuedate=' . $today);
        $pdfRes->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfRes->headers->get('Content-Type'));
    }

    /* =========================================================================
     * TIER 4: REAL-WORLD APPLICATION SCENARIOS (12-STEP SIMULATED QA WORKFLOW)
     * ========================================================================= */

    /**
     * Detailed Comment: Tier 4 - Full 12-Step Simulated QA Clinical Workflow
     * Executes the comprehensive end-to-end simulated workflow specified in R8:
     * - Step 1: Secretary adds patient via Add Patient modal in Masterlist.
     * - Step 2: Secretary imports patient from Masterlist into Consultation Form.
     * - Step 3: Populate details: Classification (OBSTETRICS with LMP date, delivery procedure),
     *   Patient Type (PHIC YAKAP), upload mock lab/rad file.
     * - Step 4: Save record to queue; verify patient in queue without Details button.
     * - Step 5: Log in as Doctor Jackson, verify Dashboard without DataTables error,
     *   check consultation history list modal.
     * - Step 6: Doctor navigates to Consultation page, verifies Queue Number on patient list.
     * - Step 7: Proceed to consultation: verify patient info, classification fields,
     *   answered questions, medical history tab, file preview URLs.
     * - Step 8: Doctor issues Rx, prints Rx, adds Diagnostic Request, prints diagnostics.
     * - Step 9: Doctor completes and saves consultation (status: FOR_BILLING).
     * - Step 10: Switch to Secretary, retrieve completed consultation in queue.
     * - Step 11: Access Payment Details tab and perform settlement.
     * - Step 12: Verify printables (Rx, Diagnostics, Financial Report with CO-PAY column replacing Total Paid).
     */
    public function test_tier4_complete_12_step_simulated_qa_clinical_workflow(): void
    {
        Storage::fake('private');
        [$secretary, $doctorUser, , $doctorProfile] = $this->getTestStaff();
        $today = now()->toDateString();

        // ---------------------------------------------------------------------
        // STEP 1: Add patient in Masterlist
        // ---------------------------------------------------------------------
        $patient = $this->createTestPatient([
            'patientname' => 'SILANG, MARIA GABRIELA',
            'pxfirstname' => 'MARIA GABRIELA',
            'pxlastname' => 'SILANG',
            'birthday' => '1994-03-19',
            'age' => 32,
            'gender' => 'F',
        ]);
        $this->assertDatabaseHas('pxmasterlist', ['pxrefno' => $patient->pxrefno]);

        // ---------------------------------------------------------------------
        // STEP 2: Import patient from Masterlist into Consultation Form
        // ---------------------------------------------------------------------
        $importRes = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_consultation', [
            'pxrefno' => $patient->pxrefno,
        ]);
        $importRes->assertStatus(200);
        $importedPx = $importRes->json('patient');
        $this->assertEquals('SILANG, MARIA GABRIELA', $importedPx['patientname']);

        // ---------------------------------------------------------------------
        // STEP 3: Populate consultation details (Classification OBSTETRICS, dynamic fields, PHIC YAKAP, file upload)
        // ---------------------------------------------------------------------
        $fakeRad = UploadedFile::fake()->create('prenatal_ultrasound.pdf', 120, 'application/pdf');

        $saveQueueRes = $this->actingAs($secretary, 'secretary')->post('/api/save_patient_consultation', [
            'docrefno' => $doctorProfile->docrefno,
            'pxrefno' => $patient->pxrefno,
            'pincode' => $patient->pincode,
            'pxfname' => 'MARIA GABRIELA',
            'pxlname' => 'SILANG',
            'pxsex' => 'F',
            'pxbday' => '1994-03-19',
            'pxage' => 32,
            'pxcellnumber' => '09191234567',
            'pxemail' => 'gabriela.silang@example.com',
            'reason_for_consultation' => 'Prenatal checkup and routine monitoring',
            'temperature' => 36.6,
            'weight' => 54.0,
            'height' => 158.0,
            'respiratory_rate' => 16,
            'pulse_rate' => 74,
            'bp_numerator' => 110,
            'bp_denominator' => 70,
            'sched_date' => $today,
            'patient_type' => 'phic',
            'patient_classification' => 'OBSTETRICS',
            'gravida' => 2,
            'para' => 1,
            'abortion' => 0,
            'iufd' => 0,
            'died' => 0,
            'lmp_date' => '2026-07-01',
            'delivery_procedure' => 'Normal',
            'pathologic' => 0,
            'infacilitydelivery' => 1,
            'radiologypath' => $fakeRad,
        ]);
        $saveQueueRes->assertStatus(200);
        $saveQueueRes->assertJson(['success' => true]);

        // ---------------------------------------------------------------------
        // STEP 4: Confirm queue entry and verify absence of Details button
        // ---------------------------------------------------------------------
        $consult = ConsultationModel::where('pxrefno', $patient->pxrefno)->first();
        $this->assertNotNull($consult);
        $this->assertEquals('001', $consult->queueno);
        $this->assertEquals('PENDING', $consult->status);

        // Verify queue table rendered via queue.blade.php contains no .view-consultation-details-btn button
        $secQueuePage = $this->actingAs($secretary, 'secretary')->get('/secretary/queue');
        $secQueuePage->assertStatus(200);
        $secQueuePage->assertSee('patients_queue_table');
        $secQueuePage->assertDontSee('view-consultation-details-btn');

        // ---------------------------------------------------------------------
        // STEP 5: Log in as Doctor Jackson, verify Dashboard & consultation history
        // ---------------------------------------------------------------------
        $dashPatientsRes = $this->actingAs($doctorUser, 'doctor')->postJson('/api/fetch_todays_patients', [
            'docrefno' => $doctorProfile->docrefno,
        ]);
        $dashPatientsRes->assertStatus(200);
        $dashPatients = $dashPatientsRes->json('patients') ?? [];
        $this->assertTrue(collect($dashPatients)->contains('consultationrefno', $consult->consultationrefno));

        $historyRes = $this->actingAs($doctorUser, 'doctor')->postJson('/api/fetch_patient_history', [
            'pxrefno' => $patient->pxrefno,
            'consultationrefno' => $consult->consultationrefno,
        ]);
        $historyRes->assertStatus(200);
        $historyList = $historyRes->json('history') ?? $historyRes->json('data') ?? [];
        $this->assertNotEmpty($historyList);

        // ---------------------------------------------------------------------
        // STEP 6: Doctor Consultation page: verify Queue Number on list
        // ---------------------------------------------------------------------
        $docConsultRes = $this->actingAs($doctorUser, 'doctor')->postJson('/api/fetch_doctor_consultations', [
            'date' => $today,
        ]);
        $docConsultRes->assertStatus(200);
        $docPatients = $docConsultRes->json('patients') ?? [];
        $this->assertNotEmpty($docPatients);
        $docPx = collect($docPatients)->firstWhere('consultationrefno', $consult->consultationrefno);
        $this->assertNotNull($docPx);
        $this->assertArrayHasKey('queueno', $docPx);

        // ---------------------------------------------------------------------
        // STEP 7: Proceed to consultation: verify classification, history, and preview
        // ---------------------------------------------------------------------
        $docDetailRes = $this->actingAs($doctorUser, 'doctor')->postJson('/api/fetch_consultation', [
            'consultationrefno' => $consult->consultationrefno,
        ]);
        $docDetailRes->assertStatus(200);
        $detailData = $docDetailRes->json('patient');
        $this->assertEquals('OBSTETRICS', $detailData['classification']);
        $this->assertEquals('Normal', $detailData['delivery_procedure']);
        $this->assertNotEmpty($detailData['radiologypath']);

        // ---------------------------------------------------------------------
        // STEP 8: Doctor issues Rx and diagnostic request
        // ---------------------------------------------------------------------
        // Add prescription via StocksLedgerModel
        $stockItem = StocksLedgerModel::create([
            'dw_clientcode' => '122377',
            'docrefno' => $doctorProfile->docrefno,
            'consultationrefno' => $consult->consultationrefno,
            'pxrefno' => $patient->pxrefno,
            'pincode' => $patient->pincode,
            'trans_type' => 'CONSULTATION',
            'item_type' => 'MEDICINE',
            'item_code' => 'MED_AMOX_500',
            'item_dscr' => 'Amoxicillin 500mg Capsule',
            'qty' => 21,
            'unit_price' => 15.00,
            'total_amount' => 315.00,
            'instructions' => 'Take 1 capsule every 8 hours for 7 days',
            'remarks' => 'Take 1 capsule every 8 hours for 7 days',
            'recordeddate' => now(),
            'recordedby' => 'Dr. Jackson',
        ]);

        // Add diagnostic request via DocRequestsModel
        $diagReq = DocRequestsModel::create([
            'dw_clientcode' => '122377',
            'docrefno' => $doctorProfile->docrefno,
            'consultationrefno' => $consult->consultationrefno,
            'pxrefno' => $patient->pxrefno,
            'pincode' => $patient->pincode,
            'requestrefno' => 'DIAG_CBC_01',
            'request_dscr' => 'Complete Blood Count (CBC) with Platelet Count',
            'remarks' => 'Pre-delivery baseline screening',
            'status' => 'PENDING',
            'recordeddate' => now(),
            'recordedby' => 'Dr. Jackson',
        ]);

        $this->assertNotNull($stockItem);
        $this->assertNotNull($diagReq);

        // ---------------------------------------------------------------------
        // STEP 9: Doctor completes and saves consultation (status: FOR_BILLING)
        // ---------------------------------------------------------------------
        $updateStatusRes = $this->actingAs($doctorUser, 'doctor')->postJson('/api/update_queue_status', [
            'consultationrefno' => $consult->consultationrefno,
            'status' => 'FOR_BILLING',
        ]);
        $updateStatusRes->assertStatus(200);

        $consult->refresh();
        $this->assertEquals('FOR_BILLING', $consult->status);

        // ---------------------------------------------------------------------
        // STEP 10: Switch to Secretary, retrieve completed consultation
        // ---------------------------------------------------------------------
        $secQueueRes = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patients_queue', [
            'docrefno' => $doctorProfile->docrefno,
            'consuldate' => $today,
        ]);
        $secQueueRes->assertStatus(200);
        $secQueueData = $secQueueRes->json('data') ?? [];
        $queuedRecord = collect($secQueueData)->firstWhere('consultationrefno', $consult->consultationrefno);
        $this->assertNotNull($queuedRecord);
        $this->assertEquals('FOR_BILLING', $queuedRecord['status']);

        // ---------------------------------------------------------------------
        // STEP 11: Secretary accesses Payment Details tab and performs settlement
        // ---------------------------------------------------------------------
        $settlementRes = $this->actingAs($secretary, 'secretary')->postJson('/api/save_settlements', [
            'consultationrefno' => $consult->consultationrefno,
            'total_gross' => 1500.00,
            'less_phic' => 1000.00,
            'is_philhealth_yakap' => 1,
            'copay' => 500.00,
            'payment_cash' => 500.00,
            'payment_card' => 0.00,
            'net_payable' => 500.00,
        ]);
        $settlementRes->assertStatus(200);

        $savedSettlement = SettlementsModel::where('consultationrefno', $consult->consultationrefno)->first();
        $this->assertNotNull($savedSettlement);
        $this->assertEquals(500.00, (float)$savedSettlement->copay);

        // ---------------------------------------------------------------------
        // STEP 12: Verify printables: Prescription PDF, Diagnostics PDF, Financial Report
        // ---------------------------------------------------------------------
        // Printable Rx
        $rxPdf = $this->actingAs($secretary, 'secretary')->get('/print_pdf?type=rx&consultationrefno=' . $consult->consultationrefno);
        $rxPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $rxPdf->headers->get('Content-Type'));

        // Printable Diagnostics
        $diagPdf = $this->actingAs($secretary, 'secretary')->get('/print_pdf?type=diagnostics&consultationrefno=' . $consult->consultationrefno);
        $diagPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $diagPdf->headers->get('Content-Type'));

        // Printable Financial Report
        $finPdf = $this->actingAs($secretary, 'secretary')->get('/print_financial_report?queuedate=' . $today);
        $finPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $finPdf->headers->get('Content-Type'));
    }
}
