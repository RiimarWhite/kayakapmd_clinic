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
use App\Models\Stocks\StocksListingModel;
use App\Models\DoctorMedicinesModel;
use App\Models\DocRequestsModel;
use App\Models\DiagnosticsMasterlistModel;
use App\Models\DocChargesModel;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Feature test suite verifying:
 * 1. Secretary and Admin access to the Secretary Queue dashboard and doctor fetching endpoints.
 * 2. Doctors dashboard and patients masterlist views rendering the detailed consultation modal (#view_consultation_details_modal).
 * 3. Doctor today's patients (/api/fetch_todays_patients) and consultation masterlist endpoints.
 * 4. Multi-role access (Secretary, Doctor, Admin) to /api/fetch_patient_medhistory providing complete consultation details:
 *    vitals, clinical notes, prescriptions (rx_items), diagnostics (diagnostic_items), and charges.
 */
class ConsultationTabAndDetailsModalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Verifies Secretary user accesses queue dashboard and retrieves assigned doctors.
     */
    public function test_secretary_queue_page_and_doctor_fetch_as_secretary(): void
    {
        $secretary = SecretaryModel::first();
        $doctor = DoctorsProfileModel::first();

        // Assign doctor to secretary
        SecretaryDoctorsModel::create([
            'secrefno' => $secretary->secrefno,
            'docrefno' => $doctor->docrefno,
            'recordedby' => 'system',
            'recordeddate' => now(),
            'active' => 1
        ]);

        $response = $this->actingAs($secretary, 'secretary')->get('/secretary/queue');
        $response->assertStatus(200);
        $response->assertSee('view_consultation_details_modal');
        $response->assertSee('medhistorytable');

        // Fetch doctors via AJAX
        $ajaxResponse = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_doctors_from_secretary');
        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertJson(['success' => true]);
        $this->assertNotEmpty($ajaxResponse->json('doctors'));
    }

    /**
     * Detailed Comment: Verifies Admin user accesses secretary queue dashboard and retrieves all doctors
     * without 500 error or null reference on $secUser->secrefno.
     */
    public function test_secretary_queue_page_and_doctor_fetch_as_admin(): void
    {
        $admin = AdminModel::first();

        $response = $this->actingAs($admin, 'admin')->get('/secretary/queue');
        $response->assertStatus(200);
        $response->assertSee('view_consultation_details_modal');

        // Fetch doctors via AJAX as admin
        $ajaxResponse = $this->actingAs($admin, 'admin')->postJson('/api/fetch_doctors_from_secretary');
        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertJson(['success' => true]);
        $this->assertNotEmpty($ajaxResponse->json('doctors'));
    }

    /**
     * Detailed Comment: Verifies Doctor dashboard and patient masterlist views properly include
     * the view_consultation_details modal component and data tables.
     */
    public function test_doctor_dashboard_and_patients_page_render_consultation_modal(): void
    {
        $doctor = DoctorModel::first();

        // Doctor dashboard check
        $dashResponse = $this->actingAs($doctor, 'doctor')->get('/doctor/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('view_consultation_details_modal');
        $dashResponse->assertSee('todays_patients_table');

        // Doctor patients masterlist check
        $patientsResponse = $this->actingAs($doctor, 'doctor')->get('/doctor/patients');
        $patientsResponse->assertStatus(200);
        $patientsResponse->assertSee('view_consultation_details_modal');
        $patientsResponse->assertSee('viewPatientModal');
        $patientsResponse->assertSee('patientMedhistoryModal');
    }

    /**
     * Detailed Comment: Verifies doctor endpoints /api/fetch_todays_patients and /api/fetch_consultation_masterlist
     * correctly return consultation records for doctor consultation review.
     */
    public function test_doctor_fetch_todays_patients_and_consultation_masterlist(): void
    {
        $doctor = DoctorModel::first();

        $consult = ConsultationModel::create([
            'consultationrefno' => 'CON_DOC_TODAY_001',
            'docrefno' => $doctor->docrefno,
            'caseno' => 'CASE_DOC_01',
            'pincode' => 'PIN_DOC_01',
            'pxrefno' => 'PX_DOC_01',
            'patientname' => 'GARCIA, MARIA',
            'reasonforconsultation' => 'Follow-up consultation',
            'impression' => 'Hypertension Stage 1',
            'finadiagnosis' => 'I10',
            'status' => 'WAITING',
            'queueno' => '001',
            'consultation_date' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        PatientMasterlist::create([
            'pxrefno' => 'PX_DOC_01',
            'pincode' => 'PIN_DOC_01',
            'patientname' => 'GARCIA, MARIA',
            'pxfirstname' => 'MARIA',
            'pxlastname' => 'GARCIA',
            'birthday' => '1985-06-15',
            'gender' => 'FEMALE',
            'mobilenumber' => '09991234567',
        ]);

        // Today's patients
        $responseToday = $this->actingAs($doctor, 'doctor')->postJson('/api/fetch_todays_patients', [
            'docrefno' => $doctor->docrefno
        ]);
        $responseToday->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $responseToday->json('count'));
        $foundToday = collect($responseToday->json('patients'))->firstWhere('consultationrefno', 'CON_DOC_TODAY_001');
        $this->assertNotNull($foundToday);

        // Consultation masterlist
        $responseMaster = $this->actingAs($doctor, 'doctor')->postJson('/api/fetch_consultation_masterlist', [
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'GARCIA']
        ]);
        $responseMaster->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $responseMaster->json('recordsTotal'));
    }

    /**
     * Detailed Comment: Verifies that /api/fetch_patient_medhistory returns comprehensive clinical payload
     * containing vitals, diagnoses, clinical notes, prescriptions, diagnostics, and itemized billing charges.
     */
    public function test_fetch_patient_medhistory_payload_includes_full_consultation_details(): void
    {
        $doctor = DoctorModel::first();
        $secretary = SecretaryModel::first();
        $admin = AdminModel::first();

        $consultationRef = 'CON_FULL_DETAILS_999';

        ConsultationModel::create([
            'consultationrefno' => $consultationRef,
            'docrefno' => $doctor->docrefno,
            'docname' => 'DR. JUAN CRUZ',
            'caseno' => 'CASE_FULL_999',
            'pincode' => 'PIN_FULL_999',
            'pxrefno' => 'PX_FULL_999',
            'patientname' => 'SANTOS, PEDRO',
            'reasonforconsultation' => 'High fever and sore throat',
            'impression' => 'Acute Pharyngitis',
            'finadiagnosis' => 'J02.9',
            'instructions' => 'Rest for 3 days, follow-up if symptoms persist',
            'foradmit' => 1,
            'foradmit_instructions' => 'Supportive therapy, oral rehydration',
            'classification' => 'HMO',
            'subclassification' => 'MAXICARE',
            'hmocode' => 'MAXI01',
            'hmoname' => 'Maxicare Healthcare',
            'status' => 'COMPLETED',
            'weight' => '72',
            'wunit' => 'kg',
            'height' => '175',
            'hunit' => 'cm',
            'temp' => '38.8',
            'tempunit' => 'C',
            'bpnumerator' => '120',
            'bpdenominator' => '80',
            'pulserate' => '88',
            'respiratoryrate' => '20',
            'consultation_date' => '2026-10-12 10:00:00'
        ]);

        // Prescription in stocks_ledger
        StocksLedgerModel::create([
            'px_consultcode_cn' => $consultationRef,
            'dw_clientcode' => 'HO1',
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_FULL_999',
            'patient_name' => 'SANTOS, PEDRO',
            'prodcode' => 'MED_AMOX_500',
            'item_dscr' => 'Amoxicillin 500mg Capsule',
            'item_grouping' => 'DRUGS AND MEDS',
            'cost_ave' => 5.00,
            'retails' => 12.00,
            'qty' => 21,
            'totalamt' => 252.00,
            'remarks' => '1 capsule every 8 hours for 7 days',
            'dispensed_status' => 'RELEASED',
            'updatedby' => 'doctor',
            'updated' => now()
        ]);

        // Diagnostic in stocks_ledger
        StocksLedgerModel::create([
            'px_consultcode_cn' => $consultationRef,
            'dw_clientcode' => 'HO1',
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_FULL_999',
            'patient_name' => 'SANTOS, PEDRO',
            'prodcode' => 'LAB_CBC',
            'item_dscr' => 'Complete Blood Count (CBC)',
            'item_grouping' => 'DIAGNOSTIC',
            'cost_ave' => 100.00,
            'retails' => 250.00,
            'qty' => 1,
            'totalamt' => 250.00,
            'remarks' => 'Routine infection screening',
            'dispensed_flag' => 0,
            'updatedby' => 'doctor',
            'updated' => now()
        ]);

        // Doctor professional fee charge in stocks_ledger
        StocksLedgerModel::create([
            'px_consultcode_cn' => $consultationRef,
            'dw_clientcode' => 'HO1',
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_FULL_999',
            'patient_name' => 'SANTOS, PEDRO',
            'prodcode' => 'PF_DR_CRUZ',
            'item_dscr' => 'Consultation Fee - Dr. Juan Cruz',
            'item_grouping' => 'PROFESSIONAL FEE',
            'cost_ave' => 600.00,
            'retails' => 600.00,
            'qty' => 1,
            'totalamt' => 600.00,
            'remarks' => 'Outpatient Consultation Fee',
            'updatedby' => 'secretary',
            'updated' => now()
        ]);

        // Verify across roles: Secretary, Doctor, Admin
        $roles = [
            ['user' => $secretary, 'guard' => 'secretary'],
            ['user' => $doctor, 'guard' => 'doctor'],
            ['user' => $admin, 'guard' => 'admin'],
        ];

        foreach ($roles as $role) {
            $res = $this->actingAs($role['user'], $role['guard'])->postJson('/api/fetch_patient_medhistory', [
                'pincode' => 'PIN_FULL_999',
                'pxrefno' => 'PX_FULL_999',
                'consultationrefno' => $consultationRef
            ]);

            $res->assertStatus(200);
            $data = $res->json();
            $this->assertNotEmpty($data['medhistory']);

            $item = collect($data['medhistory'])->firstWhere('consultationrefno', $consultationRef);
            $this->assertNotNull($item);

            // Vitals & Clinical notes
            $this->assertEquals('High fever and sore throat', $item['reasonforconsultation']);
            $this->assertEquals('Acute Pharyngitis', $item['impression']);
            $this->assertEquals('J02.9', $item['finadiagnosis']);
            $this->assertEquals('Rest for 3 days, follow-up if symptoms persist', $item['instructions']);
            $this->assertEquals(1, intval($item['foradmit']));
            $this->assertEquals('Supportive therapy, oral rehydration', $item['foradmit_instructions']);
            $this->assertEquals('72', $item['weight']);
            $this->assertEquals('175', $item['height']);
            $this->assertEquals('38.8', $item['temp']);
            $this->assertEquals('120', $item['bpnumerator']);
            $this->assertEquals('80', $item['bpdenominator']);
            $this->assertEquals('88', $item['pulserate']);
            $this->assertEquals('20', $item['respiratoryrate']);

            // Prescriptions (Rx)
            $this->assertNotEmpty($item['rx_items']);
            $rx = collect($item['rx_items'])->firstWhere('prodcode', 'MED_AMOX_500');
            $this->assertNotNull($rx);
            $this->assertEquals('Amoxicillin 500mg Capsule', $rx['item_dscr']);
            $this->assertEquals('1 capsule every 8 hours for 7 days', $rx['remarks']);
            $this->assertEquals(21, intval($rx['qty']));
            $this->assertEquals(1, intval($rx['dispensed_flag']));

            // Diagnostics
            $this->assertNotEmpty($item['diagnostic_items']);
            $diag = collect($item['diagnostic_items'])->firstWhere('prodcode', 'LAB_CBC');
            $this->assertNotNull($diag);
            $this->assertEquals('Complete Blood Count (CBC)', $diag['item_dscr']);
            $this->assertEquals('Routine infection screening', $diag['remarks']);

            // Charges & Total computation
            $this->assertNotEmpty($item['charges']);
            $this->assertGreaterThanOrEqual(3, count($item['charges']));
            $this->assertEquals(1102.00, floatval($item['charges_total']));
        }
    }

    /**
     * Detailed Comment: Verifies fetch_patient_medhistory resolves patient address from PatientMasterlist
     * (combining streetadrs, brgy, muncity, province) without MySQL 1054 column error,
     * and dynamically calculates age from birthdate when age is null on the consultation record.
     */
    public function test_fetch_patient_medhistory_resolves_address_and_demographics_from_patient_masterlist(): void
    {
        $secretary = SecretaryModel::first();
        $doctor = DoctorModel::first();
        $admin = AdminModel::first();

        $consultRef = 'CON_ADDR_TEST_01';
        $pxRef = 'PX_ADDR_TEST_01';
        $pinCode = 'PIN_ADDR_TEST_01';
        $birthDate = '1994-08-25';
        $expectedAge = Carbon::parse($birthDate)->age;

        // Authoritative patient record in pxmasterlist with composite address components
        PatientMasterlist::create([
            'pxrefno' => $pxRef,
            'pincode' => $pinCode,
            'patientname' => 'DELA CRUZ, JUAN',
            'pxfirstname' => 'JUAN',
            'pxlastname' => 'DELA CRUZ',
            'streetadrs' => '123 Rizal Street',
            'brgy' => 'Barangay San Antonio',
            'muncity' => 'Pasig City',
            'province' => 'Metro Manila',
            'birthday' => $birthDate,
            'gender' => 'MALE',
            'mobilenumber' => '09171234567',
        ]);

        // Consultation record without direct address column and with null age
        ConsultationModel::create([
            'consultationrefno' => $consultRef,
            'docrefno' => $doctor->docrefno,
            'caseno' => 'CASE_ADDR_01',
            'pincode' => $pinCode,
            'pxrefno' => $pxRef,
            'patientname' => 'DELA CRUZ, JUAN',
            'birthday' => $birthDate,
            'age' => null, // Intentionally null to test dynamic Carbon age calculation
            'gender' => null, // Intentionally null to test fallback to patient masterlist
            'mobilenumber' => null,
            'reasonforconsultation' => 'Annual Health Examination',
            'status' => 'WAITING',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        $roles = [
            ['user' => $secretary, 'guard' => 'secretary'],
            ['user' => $doctor, 'guard' => 'doctor'],
            ['user' => $admin, 'guard' => 'admin'],
        ];

        foreach ($roles as $role) {
            $response = $this->actingAs($role['user'], $role['guard'])->postJson('/api/fetch_patient_medhistory', [
                'consultationrefno' => $consultRef,
                'pxrefno' => $pxRef,
            ]);

            $response->assertStatus(200);
            $data = $response->json();
            $item = collect($data['medhistory'])->firstWhere('consultationrefno', $consultRef);
            $this->assertNotNull($item);

            // Address safely resolved from patient masterlist components without "N/A"
            $this->assertEquals('123 Rizal Street, Barangay San Antonio, Pasig City, Metro Manila', $item['address']);

            // Age dynamically calculated from birthday
            $this->assertEquals($expectedAge, $item['age']);

            // Backfilled demographics and aliases
            $this->assertEquals('MALE', $item['gender']);
            $this->assertEquals('MALE', $item['sex']);
            $this->assertEquals('09171234567', $item['mobilenumber']);
            $this->assertEquals('09171234567', $item['contactno']);
            $this->assertEquals($pinCode, $item['pincode']);
            $this->assertEquals($pinCode, $item['pin']);
            $this->assertEquals($pxRef, $item['pxrefno']);
            $this->assertEquals($pxRef, $item['ref']);
        }
    }

    /**
     * Detailed Comment: Verifies fetch_patient_medhistory generates valid preview URLs
     * for radiology and laboratory file attachments using the protected 'preview.file' route.
     */
    public function test_fetch_patient_medhistory_generates_valid_radiology_and_laboratory_preview_urls(): void
    {
        $secretary = SecretaryModel::first();

        // 1. Consultation with both radiology and laboratory attachments
        $consultWithFiles = 'CON_FILES_YES_01';
        ConsultationModel::create([
            'consultationrefno' => $consultWithFiles,
            'caseno' => 'CASE_FILES_01',
            'pxrefno' => 'PX_FILES_01',
            'pincode' => 'PIN_FILES_01',
            'patientname' => 'ATTACHMENT, PATIENT',
            'radiologypath' => 'radiology_results/chest_xray_test.png',
            'laboratorypath' => 'laboratory_results/blood_chemistry_test.pdf',
            'status' => 'WAITING',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        $res1 = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $consultWithFiles
        ]);
        $res1->assertStatus(200);
        $item1 = collect($res1->json('medhistory'))->firstWhere('consultationrefno', $consultWithFiles);
        $this->assertNotNull($item1);

        $expectedRadUrl = route('preview.file', ['path' => 'radiology_results/chest_xray_test.png']);
        $expectedLabUrl = route('preview.file', ['path' => 'laboratory_results/blood_chemistry_test.pdf']);

        $this->assertEquals($expectedRadUrl, $item1['radiology_url']);
        $this->assertEquals($expectedLabUrl, $item1['laboratory_url']);
        $this->assertStringContainsString('preview-file', $item1['radiology_url']);
        $this->assertStringContainsString('preview-file', $item1['laboratory_url']);

        // 2. Consultation without attachments returns null preview URLs
        $consultNoFiles = 'CON_FILES_NO_02';
        ConsultationModel::create([
            'consultationrefno' => $consultNoFiles,
            'caseno' => 'CASE_FILES_02',
            'pxrefno' => 'PX_FILES_02',
            'pincode' => 'PIN_FILES_02',
            'patientname' => 'NOFILES, PATIENT',
            'radiologypath' => null,
            'laboratorypath' => null,
            'status' => 'WAITING',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        $res2 = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $consultNoFiles
        ]);
        $res2->assertStatus(200);
        $item2 = collect($res2->json('medhistory'))->firstWhere('consultationrefno', $consultNoFiles);
        $this->assertNotNull($item2);
        $this->assertNull($item2['radiology_url']);
        $this->assertNull($item2['laboratory_url']);
    }

    /**
     * Detailed Comment: Verifies graceful fallback to legacy pxrxdocuments (DoctorMedicinesModel),
     * docrequests (DocRequestsModel), and doccharges (DocChargesModel) when stocks_ledger is empty.
     */
    public function test_fetch_patient_medhistory_fallback_to_legacy_prescriptions_and_diagnostics(): void
    {
        $doctor = DoctorModel::first();
        $consultLegacyRef = 'CON_LEGACY_STOCKS_EMPTY_01';

        ConsultationModel::create([
            'consultationrefno' => $consultLegacyRef,
            'docrefno' => $doctor->docrefno,
            'caseno' => 'CASE_LEGACY_01',
            'pincode' => 'PIN_LEGACY_01',
            'pxrefno' => 'PX_LEGACY_01',
            'patientname' => 'LEGACY, PATIENT',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        // Legacy medicine in pxrxdocuments
        DoctorMedicinesModel::create([
            'rxreferenceno' => 'RX_LEGACY_01',
            'consultationrefno' => $consultLegacyRef,
            'docrefno' => $doctor->docrefno,
            'medicinecode' => 'MED_PARA_500',
            'medicinename' => 'Paracetamol 500mg Tablet',
            'medicinedosage' => '1 tablet every 4 hours as needed for fever',
            'medicineduration' => '5 days',
            'medicinequantity' => 15,
            'createdby' => 'doctor',
            'createddate' => now()
        ]);

        // Legacy diagnostic in diagnostics_masterlist & docrequests
        DiagnosticsMasterlistModel::create([
            'diagnosticrefno' => 'DIAG_URINE_TEST_01',
            'diagnostic_name' => 'Routine Urinalysis',
            'diagnostic_catg' => 'LABORATORY'
        ]);

        DocRequestsModel::create([
            'consultationrefno' => $consultLegacyRef,
            'docrefno' => $doctor->docrefno,
            'requestrefno' => 'DIAG_URINE_TEST_01',
            'request_dscr' => 'Routine Urinalysis Test',
            'request_catg' => 'LABORATORY'
        ]);

        // Legacy charge in doccharges
        DocChargesModel::create([
            'consultationrefno' => $consultLegacyRef,
            'docrefno' => $doctor->docrefno,
            'servicerefno' => 'CHG_CONSULT_01',
            'servicename' => 'Outpatient Consultation Fee',
            'quantity' => 1,
            'charge' => 500.00,
            'total' => 500.00,
            'net_total' => 500.00,
        ]);

        $res = $this->actingAs($doctor, 'doctor')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $consultLegacyRef
        ]);

        $res->assertStatus(200);
        $item = collect($res->json('medhistory'))->firstWhere('consultationrefno', $consultLegacyRef);
        $this->assertNotNull($item);

        // Fallback Prescriptions (Rx)
        $this->assertNotEmpty($item['rx_items']);
        $rx = collect($item['rx_items'])->firstWhere('prodcode', 'MED_PARA_500');
        $this->assertNotNull($rx);
        $this->assertEquals('Paracetamol 500mg Tablet', $rx['item_dscr']);
        $this->assertEquals('1 tablet every 4 hours as needed for fever', $rx['remarks']);
        $this->assertEquals(15, intval($rx['qty']));
        $this->assertEquals(0, intval($rx['dispensed_flag']));

        // Fallback Diagnostics
        $this->assertNotEmpty($item['diagnostic_items']);
        $diag = collect($item['diagnostic_items'])->firstWhere('prodcode', 'DIAG_URINE_TEST_01');
        $this->assertNotNull($diag);
        $this->assertEquals('Routine Urinalysis', $diag['item_dscr']);
        $this->assertEquals('LABORATORY', $diag['category']);

        // Fallback Charges & Gross Total
        $this->assertNotEmpty($item['charges']);
        $chg = collect($item['charges'])->firstWhere('prodcode', 'CHG_CONSULT_01');
        $this->assertNotNull($chg);
        $this->assertEquals('Outpatient Consultation Fee', $chg['item_dscr']);
        $this->assertEquals(500.00, floatval($chg['totalamt']));
        $this->assertEquals(500.00, floatval($item['charges_total']));
    }

    /**
     * Detailed Comment: Verifies printable PDF endpoints GET /print_pdf?type=rx and GET /print_pdf?type=diagnostics
     * return HTTP 200 with application/pdf streamed content for Doctor, Secretary, and Admin user sessions.
     */
    public function test_print_pdf_rx_and_diagnostics_accessible_across_all_roles(): void
    {
        $doctor = DoctorModel::first();
        $secretary = SecretaryModel::first();
        $admin = AdminModel::first();

        $consultRef = 'CON_PDF_MULTIROLE_01';

        // Patient in masterlist
        PatientMasterlist::create([
            'pxrefno' => 'PX_PDF_01',
            'pincode' => 'PIN_PDF_01',
            'patientname' => 'REYES, CLARA',
            'streetadrs' => '456 Mabini Ave',
            'brgy' => 'Poblacion',
            'muncity' => 'Quezon City',
            'province' => 'Metro Manila',
            'birthday' => '1988-11-10',
            'gender' => 'FEMALE',
        ]);

        ConsultationModel::create([
            'consultationrefno' => $consultRef,
            'docrefno' => $doctor->docrefno,
            'docname' => 'DR. JUAN CRUZ',
            'caseno' => 'CASE_PDF_01',
            'pincode' => 'PIN_PDF_01',
            'pxrefno' => 'PX_PDF_01',
            'patientname' => 'REYES, CLARA',
            'birthday' => '1988-11-10',
            'reasonforconsultation' => 'Bronchitis and chronic cough',
            'impression' => 'Acute Bronchitis',
            'finadiagnosis' => 'J20.9',
            'instructions' => 'Take prescribed antibiotics with food. Avoid cold beverages.',
            'status' => 'FOR_BILLING',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        // Prescription in stocks_ledger
        StocksLedgerModel::create([
            'px_consultcode_cn' => $consultRef,
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_PDF_01',
            'patient_name' => 'REYES, CLARA',
            'prodcode' => 'MED_AZITH_500',
            'item_dscr' => 'Azithromycin 500mg Tablet',
            'item_grouping' => 'DRUGS AND MEDS',
            'cost_ave' => 25.00,
            'retails' => 45.00,
            'qty' => 3,
            'totalamt' => 135.00,
            'remarks' => '1 tablet once daily for 3 days',
            'instructions' => 'Take 1 hour before or 2 hours after meals',
            'updatedby' => 'doctor',
            'updated' => now()
        ]);

        // Diagnostic request in stocks_ledger
        StocksLedgerModel::create([
            'px_consultcode_cn' => $consultRef,
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_PDF_01',
            'patient_name' => 'REYES, CLARA',
            'prodcode' => 'DIAG_CHEST_XRAY',
            'item_dscr' => 'Chest X-Ray PA View',
            'item_grouping' => 'DIAGNOSTIC',
            'cost_ave' => 300.00,
            'retails' => 500.00,
            'qty' => 1,
            'totalamt' => 500.00,
            'remarks' => 'Evaluate lung parenchymal opacities',
            'updatedby' => 'doctor',
            'updated' => now()
        ]);

        $roles = [
            ['user' => $doctor, 'guard' => 'doctor'],
            ['user' => $secretary, 'guard' => 'secretary'],
            ['user' => $admin, 'guard' => 'admin'],
        ];

        foreach ($roles as $role) {
            // 1. Prescription (Rx) PDF streaming
            $rxResponse = $this->actingAs($role['user'], $role['guard'])
                ->get("/print_pdf?type=rx&consultationrefno={$consultRef}");

            $rxResponse->assertStatus(200);
            $this->assertEquals('application/pdf', $rxResponse->headers->get('content-type'));
            $this->assertStringStartsWith('%PDF', $rxResponse->getContent());

            // 2. Diagnostics Request PDF streaming
            $diagResponse = $this->actingAs($role['user'], $role['guard'])
                ->get("/print_pdf?type=diagnostics&consultationrefno={$consultRef}");

            $diagResponse->assertStatus(200);
            $this->assertEquals('application/pdf', $diagResponse->headers->get('content-type'));
            $this->assertStringStartsWith('%PDF', $diagResponse->getContent());
        }
    }

    /**
     * Detailed Comment: Verifies strict line-item segregation and billing calculation accuracy in PDF printouts.
     * Rx slips strictly display DRUGS AND MEDS items and exclude diagnostic procedures and professional fees.
     * Diagnostics slips strictly display DIAGNOSTIC items and exclude medicine items.
     * SOA billing slips itemize all consultation charges matching the calculated gross total.
     */
    public function test_print_pdf_strict_item_segregation_and_charges_calculation(): void
    {
        $doctor = DoctorModel::first();
        $secretary = SecretaryModel::first();
        $consultRef = 'CON_SEGREGATION_99';

        ConsultationModel::create([
            'consultationrefno' => $consultRef,
            'docrefno' => $doctor->docrefno,
            'caseno' => 'CASE_SEG_99',
            'pincode' => 'PIN_SEG_99',
            'pxrefno' => 'PX_SEG_99',
            'patientname' => 'SEGREGATION, TEST',
            'status' => 'FOR_BILLING',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        // Item 1: Prescription Medicine
        StocksLedgerModel::create([
            'px_consultcode_cn' => $consultRef,
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_SEG_99',
            'patient_name' => 'SEGREGATION, TEST',
            'prodcode' => 'MED_AMOX_500',
            'item_dscr' => 'Amoxicillin 500mg',
            'item_grouping' => 'DRUGS AND MEDS',
            'cost_ave' => 10.00,
            'retails' => 15.00,
            'qty' => 20,
            'totalamt' => 300.00,
            'remarks' => '1 capsule tid for 7 days',
            'updatedby' => 'doctor',
            'updated' => now()
        ]);

        // Item 2: Diagnostic Procedure
        StocksLedgerModel::create([
            'px_consultcode_cn' => $consultRef,
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_SEG_99',
            'patient_name' => 'SEGREGATION, TEST',
            'prodcode' => 'DIAG_LIPID_PANEL',
            'item_dscr' => 'Lipid Profile Test',
            'item_grouping' => 'DIAGNOSTIC',
            'cost_ave' => 200.00,
            'retails' => 450.00,
            'qty' => 1,
            'totalamt' => 450.00,
            'remarks' => 'Fasting blood chemistry',
            'updatedby' => 'doctor',
            'updated' => now()
        ]);

        // Item 3: Professional Fee
        StocksLedgerModel::create([
            'px_consultcode_cn' => $consultRef,
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_SEG_99',
            'patient_name' => 'SEGREGATION, TEST',
            'prodcode' => 'PF_SPECIALIST',
            'item_dscr' => 'Specialist Consultation Fee',
            'item_grouping' => 'PROFESSIONAL FEE',
            'cost_ave' => 700.00,
            'retails' => 700.00,
            'qty' => 1,
            'totalamt' => 700.00,
            'remarks' => 'Clinic visit fee',
            'updatedby' => 'secretary',
            'updated' => now()
        ]);

        // Verify API charges total computation
        $apiResponse = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $consultRef
        ]);
        $apiResponse->assertStatus(200);
        $item = collect($apiResponse->json('medhistory'))->firstWhere('consultationrefno', $consultRef);
        $this->assertNotNull($item);
        // Gross sum = 300 + 450 + 700 = 1450.00
        $this->assertEquals(1450.00, floatval($item['charges_total']));

        // Verify Rx printout strictly contains medicine item
        $rxResponse = $this->actingAs($doctor, 'doctor')->get("/print_pdf?type=rx&consultationrefno={$consultRef}");
        $rxResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $rxResponse->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $rxResponse->getContent());

        $medsInDb = StocksLedgerModel::where('px_consultcode_cn', $consultRef)
            ->where('item_grouping', 'DRUGS AND MEDS')
            ->get();
        $this->assertCount(1, $medsInDb);
        $this->assertEquals('MED_AMOX_500', $medsInDb->first()->prodcode);

        // Verify Diagnostics printout strictly contains diagnostic item
        $diagResponse = $this->actingAs($doctor, 'doctor')->get("/print_pdf?type=diagnostics&consultationrefno={$consultRef}");
        $diagResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $diagResponse->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $diagResponse->getContent());

        $diagsInDb = StocksLedgerModel::where('px_consultcode_cn', $consultRef)
            ->where('item_grouping', 'DIAGNOSTIC')
            ->get();
        $this->assertCount(1, $diagsInDb);
        $this->assertEquals('DIAG_LIPID_PANEL', $diagsInDb->first()->prodcode);

        // Verify Statement of Account (SOA) printout
        $soaResponse = $this->actingAs($secretary, 'secretary')->get("/print_pdf?type=soa&consultationrefno={$consultRef}");
        $soaResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $soaResponse->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $soaResponse->getContent());
    }
}

