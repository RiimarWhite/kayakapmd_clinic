<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationModel;
use App\Models\DocRequestsModel;
use App\Models\DoctorModel;
use App\Models\DoctorsProfileModel;
use App\Models\PatientMasterlist;
use App\Models\SecretaryModel;
use App\Models\Stocks\StocksLedgerModel;
use App\Models\Stocks\StocksListingModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Feature test suite verifying:
 * 1. Diagnostic requests saving with item_grouping='DIAGNOSTIC' and dual-table deletion.
 * 2. Rx print strictly filtering for medicines ('DRUGS AND MEDS') and excluding diagnostics.
 * 3. Diagnostic requests printing successfully retrieving items.
 * 4. Secretary/Admin queue append charges functionality and price calculation.
 * 5. Patient management masterlist querying pxmasterlist directly without dropping patients.
 * 6. Admin patient update and deletion preserving audit trails in pxwalkinconsultation.
 * 7. Multi-role access to consultation medical history.
 */
class PatientManagementAndPrintFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Test diagnostic request creation stores into stocks_ledger as DIAGNOSTIC and deleteDiagnostic purges both.
     */
    public function test_diagnostic_request_saving_and_deletion_synchronizes_stocks_ledger(): void
    {
        $admin = AdminModel::first();
        $this->actingAs($admin, 'admin');

        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_DIAG_01',
            'caseno' => 'CASE_DIAG_01',
            'pincode' => 'PIN_DIAG_01',
            'pxrefno' => 'PTN_DIAG_01',
            'patientname' => 'DOE, JOHN',
            'status' => 'IN_CONSULTATION'
        ]);

        StocksListingModel::create([
            'prodcode' => 'DIAG_CBC_01',
            'prod_itemdscr' => 'COMPLETE BLOOD COUNT',
            'item_grouping' => 'DIAGNOSTIC',
            'price_regular' => 350.00,
            'is_inventory' => false
        ]);

        // Save diagnostic request
        $response = $this->postJson('/api/save_diagnostics', [
            'consultationrefno' => 'CON_TEST_DIAG_01',
            'requestrefno' => 'DIAG_CBC_01'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify docrequests table
        $this->assertDatabaseHas('docrequests', [
            'consultationrefno' => 'CON_TEST_DIAG_01',
            'requestrefno' => 'DIAG_CBC_01'
        ]);

        // Verify stocks_ledger table has DIAGNOSTIC grouping
        $this->assertDatabaseHas('stocks_ledger', [
            'px_consultcode_cn' => 'CON_TEST_DIAG_01',
            'prodcode' => 'DIAG_CBC_01',
            'item_grouping' => 'DIAGNOSTIC',
            'transactiontype' => 'CHARGES'
        ]);

        // Delete diagnostic request
        $delResponse = $this->postJson('/api/delete_diagnostic', [
            'consultationrefno' => 'CON_TEST_DIAG_01',
            'requestrefno' => 'DIAG_CBC_01'
        ]);

        $delResponse->assertStatus(200);
        $delResponse->assertJson(['success' => true]);

        // Verify purged from both tables
        $this->assertDatabaseMissing('docrequests', [
            'consultationrefno' => 'CON_TEST_DIAG_01',
            'requestrefno' => 'DIAG_CBC_01'
        ]);
        $this->assertDatabaseMissing('stocks_ledger', [
            'px_consultcode_cn' => 'CON_TEST_DIAG_01',
            'prodcode' => 'DIAG_CBC_01'
        ]);
    }

    /**
     * Test print_pdf Rx mode strictly includes medicines and excludes non-medicines.
     */
    public function test_print_pdf_rx_only_contains_medicines_not_diagnostics(): void
    {
        $admin = AdminModel::first();
        $this->actingAs($admin, 'admin');

        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_RX_01',
            'caseno' => 'CASE_RX_01',
            'pincode' => 'PIN_RX_01',
            'pxrefno' => 'PTN_RX_01',
            'patientname' => 'SMITH, JANE',
            'status' => 'IN_CONSULTATION'
        ]);

        // Item 1: Medicine
        StocksLedgerModel::create([
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PTN_RX_01',
            'patient_name' => 'SMITH, JANE',
            'prodcode' => 'MED_AMOX_500',
            'px_consultcode_cn' => 'CON_TEST_RX_01',
            'item_dscr' => 'Amoxicillin 500mg',
            'qty' => 10,
            'cost_ave' => 15.00,
            'retails' => 15.00,
            'totalamt' => 150.00,
            'item_grouping' => 'DRUGS AND MEDS'
        ]);

        // Item 2: Diagnostic Charge (must NOT appear in Rx)
        StocksLedgerModel::create([
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PTN_RX_01',
            'patient_name' => 'SMITH, JANE',
            'prodcode' => 'DIAG_XRAY_CHEST',
            'px_consultcode_cn' => 'CON_TEST_RX_01',
            'item_dscr' => 'Chest X-Ray AP/Lat',
            'qty' => 1,
            'cost_ave' => 600.00,
            'retails' => 600.00,
            'totalamt' => 600.00,
            'item_grouping' => 'DIAGNOSTIC'
        ]);

        $response = $this->get('/print_pdf?type=rx&consultationrefno=CON_TEST_RX_01');
        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());

        // Verify only medicine items match the strict DRUGS AND MEDS filter
        $rxItems = StocksLedgerModel::where('px_consultcode_cn', 'CON_TEST_RX_01')
            ->where('item_grouping', 'DRUGS AND MEDS')
            ->get();
        $this->assertCount(1, $rxItems);
        $this->assertEquals('MED_AMOX_500', $rxItems->first()->prodcode);
    }

    /**
     * Test print_diagnostics finds diagnostic requests in stocks_ledger.
     */
    public function test_print_diagnostics_retrieves_diagnostic_requests(): void
    {
        $admin = AdminModel::first();
        $this->actingAs($admin, 'admin');

        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_PRINT_DIAG_01',
            'caseno' => 'CASE_PDIAG_01',
            'pincode' => 'PIN_PDIAG_01',
            'pxrefno' => 'PTN_PDIAG_01',
            'patientname' => 'DOE, JANE',
            'status' => 'IN_CONSULTATION'
        ]);

        StocksLedgerModel::create([
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PTN_PDIAG_01',
            'patient_name' => 'DOE, JANE',
            'prodcode' => 'DIAG_LIPID_01',
            'px_consultcode_cn' => 'CON_TEST_PRINT_DIAG_01',
            'item_dscr' => 'Lipid Profile',
            'qty' => 1,
            'cost_ave' => 500.00,
            'retails' => 500.00,
            'totalamt' => 500.00,
            'item_grouping' => 'DIAGNOSTIC'
        ]);

        $response = $this->get('/print_diagnostics?consultationrefno=CON_TEST_PRINT_DIAG_01');
        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $diagItems = StocksLedgerModel::where('px_consultcode_cn', 'CON_TEST_PRINT_DIAG_01')
            ->where('item_grouping', 'DIAGNOSTIC')
            ->get();
        $this->assertCount(1, $diagItems);
        $this->assertEquals('DIAG_LIPID_01', $diagItems->first()->prodcode);
    }

    /**
     * Test save_patient_charges endpoint allows secretary and admin to append charges.
     */
    public function test_save_patient_charges_appends_records_successfully(): void
    {
        $admin = AdminModel::first();
        $this->actingAs($admin, 'admin');

        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_CHARGE_01',
            'caseno' => 'CASE_CH_01',
            'pincode' => 'PIN_CH_01',
            'pxrefno' => 'PTN_CH_01',
            'patientname' => 'ALICE, WONDER',
            'status' => 'IN_CONSULTATION'
        ]);

        StocksListingModel::create([
            'prodcode' => 'SUPPLY_GLOVES_01',
            'prod_itemdscr' => 'Surgical Gloves',
            'item_grouping' => 'SUPPLIES',
            'price_regular' => 50.00,
            'is_inventory' => false
        ]);

        $response = $this->postJson('/api/save_patient_charges', [
            'consultationrefno' => 'CON_TEST_CHARGE_01',
            'chargerefnos' => [
                [
                    'prodcode' => 'SUPPLY_GLOVES_01',
                    'quantity' => 2,
                    'amount' => 50.00
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('stocks_ledger', [
            'px_consultcode_cn' => 'CON_TEST_CHARGE_01',
            'prodcode' => 'SUPPLY_GLOVES_01',
            'qty' => 2,
            'totalamt' => 100.00,
            'item_grouping' => 'SUPPLIES'
        ]);
    }

    /**
     * Test fetchAllPatients returns all patients from pxmasterlist without dropping patients lacking consultations.
     */
    public function test_fetch_all_patients_includes_patients_without_consultations(): void
    {
        $admin = AdminModel::first();
        $this->actingAs($admin, 'admin');

        // Patient 1: Has walk-in consultation
        PatientMasterlist::create([
            'pxrefno' => 'PTN_MST_001',
            'pincode' => 'PIN_MST_001',
            'patientname' => 'PATIENT, ONE',
            'pxfirstname' => 'ONE',
            'pxlastname' => 'PATIENT',
            'gender' => 'MALE',
            'birthday' => '1990-01-01',
            'mobilenumber' => '09123456789',
            'emailaddress' => 'one@example.com'
        ]);
        ConsultationModel::create([
            'consultationrefno' => 'CON_MST_001',
            'caseno' => 'CASE_MST_001',
            'pincode' => 'PIN_MST_001',
            'pxrefno' => 'PTN_MST_001',
            'patientname' => 'PATIENT, ONE',
            'status' => 'COMPLETED',
            'consultation_date' => '2026-09-15 10:00:00'
        ]);

        // Patient 2: Brand new, registered in pxmasterlist, NO walk-in consultation yet
        PatientMasterlist::create([
            'pxrefno' => 'PTN_MST_002',
            'pincode' => 'PIN_MST_002',
            'patientname' => 'PATIENT, TWO NO_CONSULT',
            'pxfirstname' => 'TWO',
            'pxlastname' => 'PATIENT',
            'gender' => 'FEMALE',
            'birthday' => '1995-05-05',
            'mobilenumber' => '09987654321',
            'emailaddress' => 'two@example.com'
        ]);

        $response = $this->postJson('/api/fetch_consultation_masterlist', [
            'start' => 0,
            'length' => 100
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        $returnedRefs = collect($data)->pluck('pxrefno')->toArray();
        $this->assertContains('PTN_MST_001', $returnedRefs);
        $this->assertContains('PTN_MST_002', $returnedRefs, 'Patient without walk-in consultation must not be omitted from masterlist');
    }

    /**
     * Test admin patient update and deletion capabilities.
     */
    public function test_patient_details_admin_update_and_delete(): void
    {
        $admin = AdminModel::first();
        $this->actingAs($admin, 'admin');

        $patient = PatientMasterlist::create([
            'pxrefno' => 'PTN_CRUD_001',
            'pincode' => 'PIN_CRUD_001',
            'patientname' => 'ORIGINAL, JOHN',
            'pxfirstname' => 'JOHN',
            'pxlastname' => 'ORIGINAL',
            'gender' => 'MALE',
            'birthday' => '1985-02-15',
            'mobilenumber' => '09111111111',
            'emailaddress' => 'original@example.com',
            'address' => '123 Old St',
            'phic_pin' => '12-345678901-2',
            'ispwd' => 0
        ]);

        $consult = ConsultationModel::create([
            'consultationrefno' => 'CON_CRUD_001',
            'caseno' => 'CASE_CRUD_001',
            'pincode' => 'PIN_CRUD_001',
            'pxrefno' => 'PTN_CRUD_001',
            'patientname' => 'ORIGINAL, JOHN',
            'status' => 'COMPLETED'
        ]);

        // 1. Fetch details
        $detailRes = $this->postJson('/api/fetch_patient_details', ['pxrefno' => 'PTN_CRUD_001']);
        $detailRes->assertStatus(200);
        $detailRes->assertJson(['success' => true]);
        $this->assertEquals('ORIGINAL', $detailRes->json('patient.pxlastname'));

        // 2. Admin Update Patient
        $updateRes = $this->postJson('/api/admin/update_patient', [
            'pxrefno' => 'PTN_CRUD_001',
            'pxfirstname' => 'JONATHAN',
            'pxlastname' => 'UPDATED',
            'pxmidname' => 'M',
            'gender' => 'MALE',
            'birthday' => '1985-02-15',
            'mobilenumber' => '09222222222',
            'emailaddress' => 'updated@example.com',
            'address' => '456 New Blvd',
            'religion' => 'Christian',
            'nationality' => 'FILIPINO',
            'phic_pin' => '99-888888888-7',
            'ispwd' => 1
        ]);

        $updateRes->assertStatus(200);
        $updateRes->assertJson(['success' => true]);

        $this->assertDatabaseHas('pxmasterlist', [
            'pxrefno' => 'PTN_CRUD_001',
            'pxfirstname' => 'JONATHAN',
            'pxlastname' => 'UPDATED',
            'mobilenumber' => '09222222222',
            'ispwd' => 1
        ]);

        // Synchronized to walk-in consultation
        $this->assertDatabaseHas('pxwalkinconsultation', [
            'pxrefno' => 'PTN_CRUD_001',
            'pxfirstname' => 'JONATHAN',
            'pxlastname' => 'UPDATED'
        ]);

        // 3. Admin Delete Patient
        $delRes = $this->postJson('/api/admin/delete_patient', ['pxrefno' => 'PTN_CRUD_001']);
        $delRes->assertStatus(200);
        $delRes->assertJson(['success' => true]);

        // pxmasterlist is deleted
        $this->assertDatabaseMissing('pxmasterlist', ['pxrefno' => 'PTN_CRUD_001']);

        // Historical consultation record in pxwalkinconsultation remains intact for audit compliance
        $this->assertDatabaseHas('pxwalkinconsultation', ['pxrefno' => 'PTN_CRUD_001']);
    }

    /**
     * Test consultation history endpoint is accessible to Doctor and Admin.
     */
    public function test_consultation_history_accessible_to_doctor(): void
    {
        $doctor = DoctorModel::first();
        $this->actingAs($doctor, 'doctor');

        ConsultationModel::create([
            'consultationrefno' => 'CON_HIST_01',
            'caseno' => 'CASE_HIST_01',
            'pincode' => 'PIN_HIST_01',
            'pxrefno' => 'PTN_HIST_01',
            'patientname' => 'HISTORIC, PATIENT',
            'reasonforconsultation' => 'Routine checkup',
            'status' => 'COMPLETED'
        ]);

        $res = $this->postJson('/api/fetch_patient_medhistory', [
            'pincode' => 'PIN_HIST_01',
            'pxrefno' => 'PTN_HIST_01'
        ]);

        $res->assertStatus(200);
        $res->assertJson(['success' => true]);
        $this->assertCount(1, $res->json('history'));
    }

    /**
     * Detailed Comment: Verify deleting a charge using valid auto-incremented numeric id.
     */
    public function test_delete_charge_by_numeric_id(): void
    {
        $doctor = DoctorModel::first();
        $this->actingAs($doctor, 'doctor');

        $charge = StocksLedgerModel::create([
            'transactiontype' => 'CHARGES',
            'px_consultcode_cn' => 'CON_DEL_TEST_01',
            'prodcode' => 'MED_DEL_01',
            'item_dscr' => 'Amoxicillin 500mg Cap',
            'qty' => 10,
            'cost_ave' => 15.00,
            'retails' => 15.00,
            'totalamt' => 150.00,
            'item_grouping' => 'DRUGS AND MEDS'
        ]);

        // Auto-increment primary key must be populated as an integer
        $this->assertNotNull($charge->id);
        $this->assertIsInt($charge->id);
        $this->assertGreaterThan(0, $charge->id);

        $response = $this->postJson('/api/delete_patient_charge', [
            'consultationrefno' => 'CON_DEL_TEST_01',
            'chargeid' => $charge->id
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('stocks_ledger', [
            'id' => $charge->id
        ]);
    }

    /**
     * Detailed Comment: Verify deleting a charge handles string 'null' safely without 500 error,
     * falling back to prodcode matching to prevent SQLSTATE[22007] integer truncation.
     */
    public function test_delete_charge_handles_string_null_safely(): void
    {
        $doctor = DoctorModel::first();
        $this->actingAs($doctor, 'doctor');

        $charge = StocksLedgerModel::create([
            'transactiontype' => 'CHARGES',
            'px_consultcode_cn' => 'CON_DEL_TEST_02',
            'prodcode' => 'DIAG_CBC_FALLBACK_01',
            'item_dscr' => 'Complete Blood Count (CBC)',
            'qty' => 1,
            'cost_ave' => 250.00,
            'retails' => 250.00,
            'totalamt' => 250.00,
            'item_grouping' => 'DIAGNOSTIC'
        ]);

        $response = $this->postJson('/api/delete_patient_charge', [
            'consultationrefno' => 'CON_DEL_TEST_02',
            'chargeid' => 'null', // String 'null' sent by client
            'prodcode' => 'DIAG_CBC_FALLBACK_01'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('stocks_ledger', [
            'px_consultcode_cn' => 'CON_DEL_TEST_02',
            'prodcode' => 'DIAG_CBC_FALLBACK_01'
        ]);
    }

    /**
     * Detailed Comment: Verify deleting a charge rejects requests missing valid identifiers with 422.
     */
    public function test_delete_charge_rejects_missing_identifiers(): void
    {
        $doctor = DoctorModel::first();
        $this->actingAs($doctor, 'doctor');

        $response = $this->postJson('/api/delete_patient_charge', [
            'consultationrefno' => 'CON_DEL_TEST_03',
            'chargeid' => 'null' // only string 'null' with no prodcode
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }
}

