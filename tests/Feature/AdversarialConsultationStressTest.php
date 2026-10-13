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
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Adversarial Challenge and Stress-Test Suite for KayakapMD Clinic.
 * Formulated by challenger_1 to stress-test backend endpoints, boundary data conditions,
 * missing physical files on disk, invalid/malicious query parameters, and PDF generation across roles.
 *
 * Focus Areas:
 * 1. /api/fetch_patient_medhistory with edge-case consultation fixtures:
 *    - Patient with null birthday, null address, missing contact number.
 *    - Invalid birthday string handling (e.g. 'NOT_A_DATE').
 *    - Consultation with zero charges in stocks_ledger vs consultation with legacy records in pxrxdocuments/docrequests.
 *    - Missing physical attachment files on disk (testing that /preview-file returns 404 cleanly without 500 error).
 *    - Invalid or non-existent consultationrefno, empty string, and SQL injection payloads.
 * 2. GET /print_pdf?type=rx and GET /print_pdf?type=diagnostics:
 *    - Unauthenticated access rejection (redirect to /login).
 *    - Authenticated Doctor, Secretary, and Admin user sessions returning HTTP 200 with application/pdf.
 *    - Invalid type parameter (e.g. type=unknown, path traversal attempts).
 *    - Non-existent consultation reference fallback handling.
 */
class AdversarialConsultationStressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Detailed Comment: Seed baseline system accounts (Admin, Doctor, Secretary) for authentication testing
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Helper to retrieve or create staff user models and doctor profiles.
     */
    private function getTestStaff(): array
    {
        $secretary = SecretaryModel::first();
        $doctorUser = DoctorModel::first();
        $admin = AdminModel::first();
        $doctorProfile = DoctorsProfileModel::first();

        // Assign doctor to secretary if not assigned
        if ($secretary && $doctorProfile) {
            SecretaryDoctorsModel::firstOrCreate([
                'secrefno' => $secretary->secrefno,
                'docrefno' => $doctorProfile->docrefno,
            ], [
                'recordedby' => 'system',
                'recordeddate' => now(),
                'active' => 1
            ]);
        }

        return [$secretary, $doctorUser, $admin, $doctorProfile];
    }

    /**
     * Detailed Comment: Challenge 1.1 - Patient with null birthday, null address, and missing contact number.
     * Stresses /api/fetch_patient_medhistory when patient demographic records in both pxmasterlist
     * and pxwalkinconsultation contain null or empty values. Ensures no Carbon parse crash,
     * age is set to null, address defaults safely to 'N/A', and contactno defaults to 'N/A'.
     */
    public function test_adversarial_patient_with_null_birthday_address_and_contactno(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_ADV_NULL_DEMO_01';
        $pxRef = 'PX_ADV_NULL_01';
        $pinCode = 'PIN_ADV_NULL_01';

        // Patient master with all null address fields and null birthday / phone
        PatientMasterlist::create([
            'pxrefno' => $pxRef,
            'pincode' => $pinCode,
            'patientname' => 'ANONYMOUS, PATIENT',
            'streetadrs' => null,
            'brgy' => null,
            'muncity' => null,
            'province' => null,
            'address' => null,
            'birthday' => null,
            'mobilenumber' => null,
            'gender' => 'UNKNOWN',
        ]);

        // Consultation record with null age, birthday, and contact number
        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'docname' => 'DR. TEST ADV',
            'caseno' => 'CASE_ADV_NULL_01',
            'pxrefno' => $pxRef,
            'pincode' => $pinCode,
            'patientname' => 'ANONYMOUS, PATIENT',
            'birthday' => null,
            'age' => null,
            'mobilenumber' => null,
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        $response = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $item = collect($response->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($item, 'Consultation record must be retrieved by exact consultationrefno');

        // Verify defensive defaults and null safety
        $this->assertNull($item['birthday'], 'Birthday must be null when empty on masterlist and consultation');
        $this->assertNull($item['age'], 'Age must be null when birthday cannot be evaluated');
        $this->assertEquals('N/A', $item['address'], 'Address must default to N/A without database schema error');
        $this->assertEquals('N/A', $item['contactno'], 'Contact number must default to N/A');
        $this->assertEquals('N/A', $item['mobilenumber'], 'Mobile number must default to N/A');
    }

    /**
     * Detailed Comment: Challenge 1.1b - Adversarial invalid date format in birthday string.
     * Tests that malformed birthday strings (such as 'NOT_A_DATE') do not trigger fatal unhandled exceptions
     * during age calculation, and safely default age to null via the catch(\Throwable $e) block.
     */
    public function test_adversarial_invalid_birthday_string_age_calculation_resilience(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_ADV_INVALID_BDAY_01';
        $pxRef = 'PX_ADV_INVALID_BDAY_01';

        PatientMasterlist::create([
            'pxrefno' => $pxRef,
            'pincode' => 'PIN_ADV_IBD_01',
            'patientname' => 'MALFORMED, DATE PATIENT',
            'birthday' => 'NOT_A_DATE',
            'gender' => 'MALE',
        ]);

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'pxrefno' => $pxRef,
            'patientname' => 'MALFORMED, DATE PATIENT',
            'birthday' => 'NOT_A_DATE',
            'age' => null,
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        $response = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);

        $response->assertStatus(200);
        $item = collect($response->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($item);
        // The catch(\Throwable $e) block should catch Carbon parse failures and keep age null
        $this->assertNull($item['age'], 'Age calculation must safely catch invalid date formats and remain null');
    }

    /**
     * Detailed Comment: Challenge 1.2 - Zero charges in stocks_ledger vs consultation with legacy records.
     * Compares two consultations:
     * Consultation A: Zero charges in stocks_ledger and zero legacy records -> charges_total = 0, empty lists.
     * Consultation B: Zero charges in stocks_ledger, but legacy records exist in pxrxdocuments, docrequests, and doccharges ->
     *                 seamlessly populates rx_items, diagnostic_items, charges, and charges_total.
     */
    public function test_adversarial_zero_charges_ledger_vs_legacy_data_fallback(): void
    {
        [$secretary, , , $doctorProfile] = $this->getTestStaff();

        $refNoEmpty = 'CON_ADV_EMPTY_CHARGES_01';
        $refNoLegacy = 'CON_ADV_LEGACY_CHARGES_02';

        // 1. Consultation A (Truly Empty)
        ConsultationModel::create([
            'consultationrefno' => $refNoEmpty,
            'docrefno' => $doctorProfile->docrefno,
            'patientname' => 'ZERO, CHARGES PATIENT',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        $resEmpty = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNoEmpty
        ]);

        $resEmpty->assertStatus(200);
        $itemEmpty = collect($resEmpty->json('medhistory'))->firstWhere('consultationrefno', $refNoEmpty);
        $this->assertNotNull($itemEmpty);
        $this->assertEquals(0, floatval($itemEmpty['charges_total']));
        $this->assertEmpty($itemEmpty['rx_items']);
        $this->assertEmpty($itemEmpty['prescriptions']);
        $this->assertEmpty($itemEmpty['diagnostic_items']);
        $this->assertEmpty($itemEmpty['diagnostics']);
        $this->assertEmpty($itemEmpty['charges']);

        // 2. Consultation B (Legacy Records Only)
        ConsultationModel::create([
            'consultationrefno' => $refNoLegacy,
            'docrefno' => $doctorProfile->docrefno,
            'patientname' => 'LEGACY, ARCHIVE PATIENT',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        // Legacy Rx in pxrxdocuments
        DoctorMedicinesModel::create([
            'consultationrefno' => $refNoLegacy,
            'docrefno' => $doctorProfile->docrefno,
            'medicinecode' => 'LEGACY_MED_01',
            'medicinename' => 'Cefalexin 500mg Capsule',
            'medicinedosage' => '1 capsule 3 times daily for 7 days',
            'medicineduration' => '7 days',
            'medicinequantity' => 21,
            'createdby' => 'doctor',
            'createddate' => now()
        ]);

        // Legacy Diagnostic in docrequests & diagnostics_masterlist
        DiagnosticsMasterlistModel::create([
            'diagnosticrefno' => 'LEGACY_DIAG_01',
            'diagnostic_name' => 'Fasting Blood Sugar (FBS)',
            'diagnostic_catg' => 'LABORATORY'
        ]);

        DocRequestsModel::create([
            'consultationrefno' => $refNoLegacy,
            'docrefno' => $doctorProfile->docrefno,
            'requestrefno' => 'LEGACY_DIAG_01',
            'request_dscr' => 'Fasting Blood Sugar Test',
            'request_catg' => 'LABORATORY'
        ]);

        // Legacy Charge in doccharges
        DocChargesModel::create([
            'consultationrefno' => $refNoLegacy,
            'docrefno' => $doctorProfile->docrefno,
            'servicerefno' => 'LEGACY_PF_01',
            'servicename' => 'Follow-up Consultation Fee',
            'quantity' => 1,
            'charge' => 450.00,
            'total' => 450.00,
            'net_total' => 450.00,
        ]);

        $resLegacy = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNoLegacy
        ]);

        $resLegacy->assertStatus(200);
        $itemLegacy = collect($resLegacy->json('medhistory'))->firstWhere('consultationrefno', $refNoLegacy);
        $this->assertNotNull($itemLegacy);

        // Assert fallback prescriptions
        $this->assertCount(1, $itemLegacy['rx_items']);
        $this->assertEquals('Cefalexin 500mg Capsule', $itemLegacy['rx_items'][0]['item_dscr']);
        $this->assertEquals('1 capsule 3 times daily for 7 days', $itemLegacy['rx_items'][0]['remarks']);
        $this->assertEquals(21, intval($itemLegacy['rx_items'][0]['qty']));
        $this->assertEquals(0, intval($itemLegacy['rx_items'][0]['dispensed_flag']));

        // Assert fallback diagnostics
        $this->assertCount(1, $itemLegacy['diagnostic_items']);
        $this->assertEquals('Fasting Blood Sugar (FBS)', $itemLegacy['diagnostic_items'][0]['item_dscr']);
        $this->assertEquals('LABORATORY', $itemLegacy['diagnostic_items'][0]['category']);

        // Assert fallback charges and total calculation
        $this->assertCount(1, $itemLegacy['charges']);
        $this->assertEquals('Follow-up Consultation Fee', $itemLegacy['charges'][0]['item_dscr']);
        $this->assertEquals(450.00, floatval($itemLegacy['charges'][0]['totalamt']));
        $this->assertEquals(450.00, floatval($itemLegacy['charges_total']));
    }

    /**
     * Detailed Comment: Challenge 1.3 - Missing physical attachment files on disk.
     * Stresses the file preview endpoint (/preview-file/{path}) when the database contains
     * radiology and laboratory paths pointing to non-existent files in storage/app/private.
     * Verifies that the endpoint returns HTTP 404 cleanly without throwing an unhandled 500 error.
     */
    public function test_adversarial_missing_attachment_files_on_disk_preview_file_returns_404(): void
    {
        [$secretary, $doctorUser, $admin, $doctorProfile] = $this->getTestStaff();

        $refNo = 'CON_ADV_ATTACH_404_01';
        $nonExistentRadPath = 'radiology/non_existent_scan_' . uniqid() . '.png';
        $nonExistentLabPath = 'laboratory/non_existent_lab_' . uniqid() . '.pdf';

        ConsultationModel::create([
            'consultationrefno' => $refNo,
            'docrefno' => $doctorProfile->docrefno,
            'patientname' => 'ATTACHMENT, TEST PATIENT',
            'radiologypath' => $nonExistentRadPath,
            'laboratorypath' => $nonExistentLabPath,
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        // 1. Verify /api/fetch_patient_medhistory generates valid preview URLs
        $medhistoryRes = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => $refNo
        ]);

        $medhistoryRes->assertStatus(200);
        $item = collect($medhistoryRes->json('medhistory'))->firstWhere('consultationrefno', $refNo);
        $this->assertNotNull($item);
        $this->assertStringContainsString('preview-file', $item['radiology_url']);
        $this->assertStringContainsString('preview-file', $item['laboratory_url']);

        // 2. Authenticated Doctor accessing missing file returns clean HTTP 404 (not 500)
        $docRes = $this->actingAs($doctorUser, 'doctor')->get("/preview-file/{$nonExistentRadPath}");
        $docRes->assertStatus(404);

        // 3. Authenticated Secretary accessing missing file returns clean HTTP 404 (not 500)
        $secRes = $this->actingAs($secretary, 'secretary')->get("/preview-file/{$nonExistentLabPath}");
        $secRes->assertStatus(404);

        // 4. Authenticated Admin accessing via project subfolder route returns clean HTTP 404
        $adminRes = $this->actingAs($admin, 'admin')->get("/kayakapmd_clinic/preview-file/{$nonExistentLabPath}");
        $adminRes->assertStatus(404);
    }

    /**
     * Detailed Comment: Challenge 1.3b - Unauthenticated access to preview-file endpoint.
     * Verifies that unauthenticated guest requests to preview-file are rejected with 302 redirect.
     */
    public function test_adversarial_preview_file_unauthenticated_access_rejected(): void
    {
        $nonExistentPath = 'radiology/unauth_file_' . uniqid() . '.png';
        $unauthRes = $this->get("/preview-file/{$nonExistentPath}");
        $unauthRes->assertStatus(302)->assertRedirect('/login');
    }

    /**
     * Detailed Comment: Challenge 1.4 - Invalid or non-existent consultationrefno, empty string, and SQL injection payloads.
     * Verifies that unusual or malicious inputs do not trigger database syntax errors or 500 crashes.
     */
    public function test_adversarial_invalid_and_nonexistent_consultationrefno(): void
    {
        [$secretary] = $this->getTestStaff();

        // 1. Non-existent reference
        $res1 = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => 'NON_EXISTENT_REF_99999999'
        ]);
        $res1->assertStatus(200);
        $this->assertTrue($res1->json('success'));
        $this->assertEmpty($res1->json('medhistory'));
        $this->assertEquals(0, $res1->json('recordsTotal'));

        // 2. Empty string consultationrefno
        $res2 = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => ''
        ]);
        $res2->assertStatus(200);
        $this->assertTrue($res2->json('success'));
        $this->assertEmpty($res2->json('medhistory'));

        // 3. SQL injection payload attempt
        $res3 = $this->actingAs($secretary, 'secretary')->postJson('/api/fetch_patient_medhistory', [
            'consultationrefno' => "' OR '1'='1"
        ]);
        $res3->assertStatus(200);
        $this->assertTrue($res3->json('success'));
        $this->assertEmpty($res3->json('medhistory'), 'SQL injection payload must be sanitized and match no records');
    }

    /**
     * Detailed Comment: Challenge 2.1 - Unauthenticated access to print_pdf endpoints.
     * Stresses GET /print_pdf?type=rx and GET /print_pdf?type=diagnostics without an active user session.
     * Asserts that requests are rejected with HTTP 302 redirecting to /login.
     */
    public function test_adversarial_print_pdf_unauthenticated_access_rejection(): void
    {
        // 1. Rx Print
        $rxResponse = $this->get('/print_pdf?type=rx&consultationrefno=CON_GUEST_01');
        $rxResponse->assertStatus(302)->assertRedirect('/login');

        // 2. Diagnostics Print
        $diagResponse = $this->get('/print_pdf?type=diagnostics&consultationrefno=CON_GUEST_01');
        $diagResponse->assertStatus(302)->assertRedirect('/login');

        // 3. Alternative print_diagnostics route
        $altDiagResponse = $this->get('/print_diagnostics?consultationrefno=CON_GUEST_01');
        $altDiagResponse->assertStatus(302)->assertRedirect('/login');
    }

    /**
     * Detailed Comment: Challenge 2.2 - Authenticated Doctor, Secretary, and Admin user sessions.
     * Verifies that GET /print_pdf?type=rx and GET /print_pdf?type=diagnostics return HTTP 200
     * with Content-Type: application/pdf and start with '%PDF' for Doctor, Secretary, and Admin.
     */
    public function test_adversarial_print_pdf_multirole_session_streaming(): void
    {
        [$secretary, $doctorUser, $admin, $doctorProfile] = $this->getTestStaff();

        $consultRef = 'CON_ADV_PDF_ROLES_01';

        PatientMasterlist::create([
            'pxrefno' => 'PX_ADV_PDF_01',
            'pincode' => 'PIN_ADV_PDF_01',
            'patientname' => 'SANTOS, CARMINA',
            'streetadrs' => '123 Rizal Street',
            'brgy' => 'San Isidro',
            'muncity' => 'Makati City',
            'province' => 'Metro Manila',
            'birthday' => '1992-05-15',
            'gender' => 'FEMALE',
        ]);

        ConsultationModel::create([
            'consultationrefno' => $consultRef,
            'docrefno' => $doctorProfile->docrefno,
            'docname' => 'DR. TEST PHYSICIAN',
            'caseno' => 'CASE_ADV_PDF_01',
            'pincode' => 'PIN_ADV_PDF_01',
            'pxrefno' => 'PX_ADV_PDF_01',
            'patientname' => 'SANTOS, CARMINA',
            'birthday' => '1992-05-15',
            'instructions' => 'Take all medications after meals.',
            'status' => 'FOR_BILLING',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        StocksLedgerModel::create([
            'px_consultcode_cn' => $consultRef,
            'transactiontype' => 'CHARGES',
            'prodcode' => 'MED_CETIRIZINE_10',
            'item_dscr' => 'Cetirizine 10mg Tablet',
            'item_grouping' => 'DRUGS AND MEDS',
            'cost_ave' => 5.00,
            'retails' => 15.00,
            'qty' => 10,
            'totalamt' => 150.00,
            'remarks' => '1 tablet once daily at bedtime',
            'instructions' => 'Take with water before sleeping',
            'updatedby' => 'doctor',
            'updated' => now()
        ]);

        StocksLedgerModel::create([
            'px_consultcode_cn' => $consultRef,
            'transactiontype' => 'CHARGES',
            'prodcode' => 'DIAG_CBC',
            'item_dscr' => 'Complete Blood Count (CBC)',
            'item_grouping' => 'DIAGNOSTIC',
            'cost_ave' => 80.00,
            'retails' => 250.00,
            'qty' => 1,
            'totalamt' => 250.00,
            'remarks' => 'With platelet count',
            'updatedby' => 'doctor',
            'updated' => now()
        ]);

        $roles = [
            ['user' => $doctorUser, 'guard' => 'doctor', 'label' => 'Doctor'],
            ['user' => $secretary, 'guard' => 'secretary', 'label' => 'Secretary'],
            ['user' => $admin, 'guard' => 'admin', 'label' => 'Admin'],
        ];

        foreach ($roles as $role) {
            // Test Rx PDF streaming
            $rxRes = $this->actingAs($role['user'], $role['guard'])
                ->get("/print_pdf?type=rx&consultationrefno={$consultRef}");

            $rxRes->assertStatus(200, "{$role['label']} must successfully stream Rx PDF");
            $this->assertStringContainsString('application/pdf', $rxRes->headers->get('Content-Type'));
            $this->assertStringStartsWith('%PDF', $rxRes->getContent(), "{$role['label']} Rx stream must produce valid PDF binary");

            // Test Diagnostics PDF streaming
            $diagRes = $this->actingAs($role['user'], $role['guard'])
                ->get("/print_pdf?type=diagnostics&consultationrefno={$consultRef}");

            $diagRes->assertStatus(200, "{$role['label']} must successfully stream Diagnostics PDF");
            $this->assertStringContainsString('application/pdf', $diagRes->headers->get('Content-Type'));
            $this->assertStringStartsWith('%PDF', $diagRes->getContent(), "{$role['label']} Diagnostics stream must produce valid PDF binary");
        }
    }

    /**
     * Detailed Comment: Challenge 2.3 - Invalid or unexpected type parameter in GET /print_pdf.
     * Tests behavior when client provides:
     * 1. type=unknown: Default fallbacks should safely render without throwing a 500 server error.
     * 2. Path traversal attack in type parameter: type=../../evil.
     * Verifies that the endpoint does not attempt file inclusion or crash.
     */
    public function test_adversarial_print_pdf_invalid_type_parameter_handling(): void
    {
        [$secretary, $doctorUser] = $this->getTestStaff();

        $consultRef = 'CON_ADV_TYPE_TEST_01';

        ConsultationModel::create([
            'consultationrefno' => $consultRef,
            'docrefno' => $doctorUser->docrefno,
            'patientname' => 'TRAVERSAL, TEST PATIENT',
            'status' => 'COMPLETED',
            'consultation_date' => now()->format('Y-m-d H:i:s')
        ]);

        // 1. type=unknown fallback
        $unknownTypeRes = $this->actingAs($doctorUser, 'doctor')
            ->get("/print_pdf?type=unknown&consultationrefno={$consultRef}");

        $unknownTypeRes->assertStatus(200, 'Invalid type=unknown should safely fallback without 500 error');
        $this->assertStringContainsString('application/pdf', $unknownTypeRes->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $unknownTypeRes->getContent());

        // 2. type=../../path_traversal attempt
        $traversalTypeRes = $this->actingAs($secretary, 'secretary')
            ->get("/print_pdf?type=../../etc/passwd&consultationrefno={$consultRef}");

        $traversalTypeRes->assertStatus(200, 'Path traversal parameter in type must not crash or leak files');
        $this->assertStringContainsString('application/pdf', $traversalTypeRes->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $traversalTypeRes->getContent());
    }

    /**
     * Detailed Comment: Challenge 2.4 (Empirical Discovery) - Non-existent consultation reference during PDF generation.
     * Empirically captures that when requesting a PDF for a non-existent consultationrefno,
     * DoctorController::printPDF falls back to a dummy patient stdClass object that lacks 'finadiagnosis'.
     * In resources/views/printables/rx_print.blade.php:195, the expression $patient->finadiagnosis triggers
     * 'ErrorException: Undefined property: stdClass::$finadiagnosis', returning HTTP 500.
     * This test documents the empirical 500 response as a confirmed adversarial finding.
     */
    public function test_adversarial_print_pdf_nonexistent_consultationrefno_behavior(): void
    {
        [, $doctorUser] = $this->getTestStaff();

        $nonExistentRef = 'NON_EXISTENT_PDF_REF_999999';

        $response = $this->actingAs($doctorUser, 'doctor')
            ->get("/print_pdf?type=rx&consultationrefno={$nonExistentRef}");

        // Empirically confirmed finding: returns 500 due to Undefined property: stdClass::$finadiagnosis in Blade view
        $response->assertStatus(500);
        $this->assertStringContainsString('Undefined property: stdClass::$finadiagnosis', $response->getContent());
    }
}
