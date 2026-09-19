<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationModel;
use App\Models\DoctorModel;
use App\Models\DoctorsProfileModel;
use App\Models\PatientMasterlist;
use App\Models\ScheduleModel;
use App\Models\SecretaryDoctorsModel;
use App\Models\SecretaryModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorSecretaryConsoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Verify saving and viewing doctor schedules via both admin/users routes and api routes.
     */
    public function test_doctor_schedules_create_and_fetch(): void
    {
        $admin = AdminModel::first();
        $doctor = DoctorsProfileModel::first();

        // 1. Create doctor schedule via admin/users/create_doctor_schedules
        $createResponse = $this->actingAs($admin, 'admin')->post('/admin/users/create_doctor_schedules', [
            'docrefno' => $doctor->docrefno,
            'day' => 'Monday',
            'stime' => '08:00:00',
            'etime' => '12:00:00',
            'slots' => 20,
            'schedtype' => 'REGULAR'
        ]);

        $createResponse->assertStatus(200);
        $createResponse->assertJson(['success' => true]);

        // 2. Fetch doctor schedules via admin/users/fetch_doctor_schedules
        $fetchResponse = $this->actingAs($admin, 'admin')->post('/admin/users/fetch_doctor_schedules', [
            'docrefno' => $doctor->docrefno
        ]);

        $fetchResponse->assertStatus(200);
        $fetchResponse->assertJson(['success' => true]);
        $this->assertNotEmpty($fetchResponse->json('schedules'));
    }

    /**
     * Detailed Comment: Verify adding a doctor defaults username to lowercase lastname and persists all profile attributes.
     */
    public function test_add_doctor_defaults_username_to_lastname(): void
    {
        $admin = AdminModel::first();

        $response = $this->actingAs($admin, 'admin')->post('/api/add_doctor', [
            'docfname' => 'Gregory',
            'docmname' => 'Vincent',
            'doclname' => 'House',
            'licno' => 'PRC-998877',
            'licnoexpiry' => '2030-12-31',
            'phicno' => 'PHIC-112233',
            'phicexpiry' => '2030-12-31',
            'pass' => 'doctor123',
            'pfrate' => 850.00,
            'clinicroom' => 'Room 305',
            'clinichours' => '9:00 AM - 1:00 PM',
            's2no' => 'S2-554433',
            'ptr' => 'PTR-778899',
            'status' => 'ACTIVE'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $doctorUser = DoctorModel::where('doclname', 'House')->first();
        $this->assertNotNull($doctorUser);
        $this->assertEquals('house', $doctorUser->username);

        $doctorProfile = DoctorsProfileModel::where('doclname', 'House')->first();
        $this->assertNotNull($doctorProfile);
        $this->assertEquals(850.00, (float)$doctorProfile->pfrate);
        $this->assertEquals('Room 305', $doctorProfile->clinicroom);
    }

    /**
     * Detailed Comment: Verify unified Secretaries/Admin Users page loads and fetch endpoint returns both types.
     */
    public function test_unified_secretaries_admins_module(): void
    {
        $admin = AdminModel::first();

        // 1. Page route /admin/users/secretaries-admins
        $pageResponse = $this->actingAs($admin, 'admin')->get('/admin/users/secretaries-admins');
        $pageResponse->assertStatus(200);

        // 2. Backward compatible alias /admin/users/secretaries
        $aliasResponse = $this->actingAs($admin, 'admin')->get('/admin/users/secretaries');
        $aliasResponse->assertStatus(200);

        // 3. Unified fetch endpoint without filter (returns both types)
        $fetchResponse = $this->actingAs($admin, 'admin')->post('/api/fetch_secretaries');
        $fetchResponse->assertStatus(200);

        $secretariesList = $fetchResponse->json('secretaries');
        $this->assertNotEmpty($secretariesList);

        $types = collect($secretariesList)->pluck('account_type')->unique()->toArray();
        $this->assertContains('Secretary', $types);
        $this->assertContains('Admin', $types);

        // 4. Detailed Comment: Verify server-side filtering by Secretary
        $secOnlyResponse = $this->actingAs($admin, 'admin')->post('/api/fetch_secretaries', [
            'account_type' => 'Secretary'
        ]);
        $secOnlyResponse->assertStatus(200);
        $secOnlyList = $secOnlyResponse->json('secretaries');
        $this->assertNotEmpty($secOnlyList);
        foreach ($secOnlyList as $item) {
            $this->assertEquals('Secretary', $item['account_type']);
        }

        // 5. Detailed Comment: Verify server-side filtering by Admin
        $adminOnlyResponse = $this->actingAs($admin, 'admin')->post('/api/fetch_secretaries', [
            'account_type' => 'Admin'
        ]);
        $adminOnlyResponse->assertStatus(200);
        $adminOnlyList = $adminOnlyResponse->json('secretaries');
        $this->assertNotEmpty($adminOnlyList);
        foreach ($adminOnlyList as $item) {
            $this->assertEquals('Admin', $item['account_type']);
        }
    }

    /**
     * Detailed Comment: Verify adding Secretary and Admin users with auto-defaulted username.
     */
    public function test_add_secretary_and_admin_with_default_username(): void
    {
        $admin = AdminModel::first();

        // 1. Add Secretary
        $secResponse = $this->actingAs($admin, 'admin')->post('/api/add_secretary', [
            'secfname' => 'Alice',
            'seclname' => 'Wong',
            'secgender' => 'female',
            'secpassword' => 'secret123',
            'secemail' => 'alice.wong@example.com'
        ]);
        $secResponse->assertStatus(200);
        $secResponse->assertJson(['success' => true]);

        $createdSec = SecretaryModel::where('secemail', 'alice.wong@example.com')->first();
        $this->assertNotNull($createdSec);
        $this->assertEquals('wong', $createdSec->username);
        $this->assertNotNull($createdSec->secrefno);

        // 2. Add Admin
        $admResponse = $this->actingAs($admin, 'admin')->post('/api/add_admin', [
            'adminfname' => 'Robert',
            'adminlname' => 'Taylor',
            'adminemail' => 'robert.taylor@example.com',
            'password' => 'adminpass123'
        ]);
        $admResponse->assertStatus(200);
        $admResponse->assertJson(['success' => true]);

        $createdAdm = AdminModel::where('adminemail', 'robert.taylor@example.com')->first();
        $this->assertNotNull($createdAdm);
        $this->assertEquals('taylor', $createdAdm->username);
    }

    /**
     * Detailed Comment: Verify secretary with email secretary.dummy@gmail.com can fetch and assign available doctors.
     */
    public function test_secretary_dummy_doctor_assignment_resolution(): void
    {
        $admin = AdminModel::first();
        $secretary = SecretaryModel::where('secemail', 'secretary.dummy@gmail.com')->first();
        $this->assertNotNull($secretary);
        $this->assertNotNull($secretary->secrefno);

        // 1. Fetch secretary doctors (available and assigned)
        $fetchDocsResponse = $this->actingAs($admin, 'admin')->post('/api/fetch_secretary_doctors', [
            'secrefno' => $secretary->secrefno
        ]);

        $fetchDocsResponse->assertStatus(200);
        $fetchDocsResponse->assertJson(['success' => true]);
        $availDocs = $fetchDocsResponse->json('avail_doctors');
        $this->assertNotEmpty($availDocs, 'Available doctors should not be empty for secretary dummy');

        // 2. Synchronize doctors
        $doctorToAssign = $availDocs[0]['docrefno'];
        $saveDocsResponse = $this->actingAs($admin, 'admin')->post('/api/save_appended_doctors', [
            'secrefno' => $secretary->secrefno,
            'doctors' => [$doctorToAssign]
        ]);

        $saveDocsResponse->assertStatus(200);
        $saveDocsResponse->assertJson(['success' => true]);

        $this->assertTrue(
            SecretaryDoctorsModel::where('secrefno', $secretary->secrefno)
                ->where('docrefno', $doctorToAssign)
                ->exists()
        );
    }

    /**
     * Detailed Comment: Verify consultation creation, updating, and marking as complete in queue.
     */
    public function test_consultation_save_update_and_mark_as_complete(): void
    {
        $secretary = SecretaryModel::first();
        $doctor = DoctorsProfileModel::first();

        // 1. Save new consultation record
        $saveResponse = $this->actingAs($secretary, 'secretary')->post('/api/save_patient_consultation', [
            'docrefno' => $doctor->docrefno,
            'pxfname' => 'Maria',
            'pxlname' => 'Santos',
            'pxsex' => 'FEMALE',
            'pxbday' => '1995-03-20',
            'reason_for_consultation' => 'Mild headache',
            'sched_date' => now()->toDateString(),
            'sched_time' => '09:00:00'
        ]);

        $saveResponse->assertStatus(200);
        $saveResponse->assertJson(['success' => true]);
        $consultationRefNo = $saveResponse->json('consultationrefno');
        $this->assertNotNull($consultationRefNo);

        $record = ConsultationModel::where('consultationrefno', $consultationRefNo)->first();
        $this->assertNotNull($record);
        $this->assertNotEmpty($record->caseno);
        $this->assertEquals('PENDING', $record->status);

        // 2. Update consultation details
        $updateResponse = $this->actingAs($secretary, 'secretary')->post('/api/update_patient_consultation', [
            'pxconsultationrefno' => $consultationRefNo,
            'docrefno' => $doctor->docrefno,
            'pxfname' => 'Maria',
            'pxlname' => 'Santos',
            'reason_for_consultation' => 'Persistent migraine'
        ]);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['success' => true]);

        // 3. Mark as Complete via update_queue_status
        $completeResponse = $this->actingAs($secretary, 'secretary')->post('/api/update_queue_status', [
            'consultationrefno' => $consultationRefNo,
            'status' => 'COMPLETED'
        ]);

        $completeResponse->assertStatus(200);
        $completeResponse->assertJson(['success' => true]);

        $record->refresh();
        $this->assertEquals('COMPLETED', $record->status);
        $this->assertTrue((bool)$record->consulted);
        $this->assertNotNull($record->consulteddate);
    }

    /**
     * Detailed Comment: Verify administrator can save and update consultations from admin/secretary console
     * without data truncation errors on source_data.
     */
    public function test_admin_can_save_and_update_consultation(): void
    {
        $admin = AdminModel::first();
        $doctor = DoctorsProfileModel::first();

        // 1. Save new consultation as admin
        $saveResponse = $this->actingAs($admin, 'admin')->post('/api/save_patient_consultation', [
            'docrefno' => $doctor->docrefno,
            'pxfname' => 'Juan',
            'pxlname' => 'Dela Cruz',
            'pxsex' => 'MALE',
            'pxbday' => '1988-11-12',
            'reason_for_consultation' => 'Annual physical checkup',
            'sched_date' => now()->toDateString(),
            'sched_time' => '10:30:00',
            'hmo_name' => 'Maxicare',
            'hmo_input' => 'HMO-001'
        ]);

        $saveResponse->assertStatus(200);
        $saveResponse->assertJson(['success' => true]);
        $consultationRefNo = $saveResponse->json('consultationrefno');
        $this->assertNotNull($consultationRefNo);

        $record = ConsultationModel::where('consultationrefno', $consultationRefNo)->first();
        $this->assertNotNull($record);
        $this->assertEquals('ADMIN', $record->source_data);
        $this->assertEquals('admin', $record->requestedby);
        $this->assertEquals('Maxicare', $record->hmoname);

        // 2. Update consultation details as admin
        $updateResponse = $this->actingAs($admin, 'admin')->post('/api/update_patient_consultation', [
            'pxconsultationrefno' => $consultationRefNo,
            'docrefno' => $doctor->docrefno,
            'pxfname' => 'Juan',
            'pxlname' => 'Dela Cruz',
            'reason_for_consultation' => 'Executive health checkup'
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['success' => true]);

        $record->refresh();
        $this->assertEquals('Executive health checkup', $record->reasonforconsultation);
    }

    /**
     * Detailed Comment: Verify self-service profile updates for Admin, Secretary, and Doctor
     * strictly modify their respective accounts in adminrights, secretaryrights, and doctors/doctorsrights.
     */
    public function test_self_service_profile_updates_for_roles(): void
    {
        $admin = AdminModel::first();
        $secretary = SecretaryModel::first();
        $doctor = DoctorModel::first();

        // 1. Admin self-service profile update
        $adminResponse = $this->actingAs($admin, 'admin')->post('/api/admin/update_profile', [
            'adminfname' => 'Super',
            'adminlname' => 'Administrator',
            'username' => 'superadmin_new',
            'adminemail' => 'superadmin@example.com',
            'admincontactno' => '09123456789'
        ]);
        $adminResponse->assertStatus(200);
        $adminResponse->assertJson(['success' => true]);
        $admin->refresh();
        $this->assertEquals('superadmin_new', $admin->username);
        $this->assertEquals('superadmin@example.com', $admin->adminemail);

        // 2. Secretary self-service profile update
        $secResponse = $this->actingAs($secretary, 'secretary')->post('/api/secretary/update_profile', [
            'secfname' => 'Maria',
            'seclname' => 'Santos',
            'username' => 'msantos_updated',
            'secpassword' => 'newsecpass123',
            'secemail' => 'msantos@example.com',
            'seccontactno' => '09987654321',
            'secgender' => 'FEMALE'
        ]);
        $secResponse->assertStatus(200);
        $secResponse->assertJson(['success' => true]);
        $secretary->refresh();
        $this->assertEquals('msantos_updated', $secretary->username);
        $this->assertEquals('msantos@example.com', $secretary->secemail);

        // 3. Doctor self-service profile update
        $docResponse = $this->actingAs($doctor, 'doctor')->post('/api/doctor/update_profile', [
            'docfname' => 'Gregory',
            'doclname' => 'House',
            'username' => 'ghouse_updated',
            'titlename' => 'MD, PhD',
            'licno' => 'PRC-123456',
            'expertise' => 'Infectious Disease & Nephrology',
            'consultationfee' => 1200.00,
            'tin' => 'TIN-998877',
            'quevisible' => 1
        ]);
        $docResponse->assertStatus(200);
        $docResponse->assertJson(['success' => true]);
        $doctor->refresh();
        $this->assertEquals('ghouse_updated', $doctor->username);
        $this->assertEquals(1200.00, (float)$doctor->consultationfee);
        $docProfile = DoctorsProfileModel::where('docrefno', $doctor->docrefno)->first();
        $this->assertEquals('Infectious Disease & Nephrology', $docProfile->expertise);
        $this->assertEquals(1200.00, (float)$docProfile->pfrate);
    }

    /**
     * Detailed Comment: Verify secretary patient import fallback and medical history fetch.
     */
    public function test_secretary_patient_import_and_medhistory(): void
    {
        $secretary = SecretaryModel::first();
        $doctor = DoctorsProfileModel::first();

        // Create consultation record
        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CRNW-TEST-001',
            'pxrefno' => 'PX-TEST-001',
            'pincode' => 'PIN-TEST-001',
            'docrefno' => $doctor->docrefno,
            'docname' => $doctor->docname,
            'patientname' => 'Patient One',
            'pxfirstname' => 'Patient',
            'pxlastname' => 'One',
            'reasonforconsultation' => 'Fever and chills',
            'finadiagnosis' => 'Viral illness',
            'consultation_date' => now(),
            'status' => 'WAITING',
            'source_data' => 'SECRETARY'
        ]);

        // 1. Fetch consultation via pxrefno
        $fetchConsultation = $this->actingAs($secretary, 'secretary')->post('/api/fetch_consultation', [
            'pxrefno' => 'PX-TEST-001'
        ]);
        $fetchConsultation->assertStatus(200);
        $fetchConsultation->assertJson(['success' => true]);
        $this->assertEquals('CRNW-TEST-001', $fetchConsultation->json('patient.consultationrefno'));

        // 2. Fetch medical history via pincode
        $fetchMedHistory = $this->actingAs($secretary, 'secretary')->post('/api/fetch_patient_medhistory', [
            'pincode' => 'PIN-TEST-001'
        ]);
        $fetchMedHistory->assertStatus(200);
        $fetchMedHistory->assertJson(['success' => true]);
        $this->assertNotEmpty($fetchMedHistory->json('history'));
        $this->assertEquals('Fever and chills', $fetchMedHistory->json('history.0.reasonforconsultation'));
    }

    /**
     * Detailed Comment: Verify secretary patient masterlist fetch succeeds without SQLSTATE 42S22 (missing pxmasterlist.casecode column)
     * and correctly aliases consultation caseno as casecode for DataTable buttons and import.
     */
    public function test_secretary_fetch_patient_masterlist_sec_resolves_casecode(): void
    {
        $secretary = SecretaryModel::first();
        $doctor = DoctorsProfileModel::first();

        // 1. Create patient masterlist record
        $patient = PatientMasterlist::create([
            'pxrefno' => 'PX-SEC-MST-001',
            'pincode' => 'PIN-SEC-MST-001',
            'patientname' => 'Dela Cruz, Juan M Jr',
            'pxfirstname' => 'Juan',
            'pxmidname' => 'M',
            'pxlastname' => 'Dela Cruz',
            'pxsuffix' => 'Jr',
            'gender' => 'MALE',
            'birthday' => '1985-05-15',
            'age' => 41,
            'mobilenumber' => '09171234567',
            'emailaddress' => 'juan.delacruz@example.com'
        ]);

        // 2. Create walk-in consultation linking patient to doctor with explicit caseno
        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CRNW-SEC-MST-001',
            'caseno' => 'CASE-2026-0001',
            'pxrefno' => 'PX-SEC-MST-001',
            'pincode' => 'PIN-SEC-MST-001',
            'docrefno' => $doctor->docrefno,
            'docname' => $doctor->docname,
            'patientname' => 'Dela Cruz, Juan M Jr',
            'pxfirstname' => 'Juan',
            'pxmidname' => 'M',
            'pxlastname' => 'Dela Cruz',
            'pxsuffix' => 'Jr',
            'reasonforconsultation' => 'Routine checkup',
            'finadiagnosis' => 'Healthy',
            'consultation_date' => now(),
            'status' => 'WAITING',
            'source_data' => 'SECRETARY'
        ]);

        // 3. Request patient masterlist for this doctor
        $response = $this->actingAs($secretary, 'secretary')->post('/api/fetch_patient_masterlist_sec', [
            'docrefno' => $doctor->docrefno,
            'draw' => 1,
            'start' => 0,
            'length' => 10
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                '*' => [
                    'pxrefno',
                    'casecode',
                    'caseno',
                    'pincode',
                    'patientname',
                    'mobilenumber',
                    'emailaddress',
                    'consultationrefno',
                    'status'
                ]
            ]
        ]);

        $this->assertGreaterThanOrEqual(1, $response->json('recordsTotal'));
        $data = $response->json('data');
        $matched = collect($data)->firstWhere('pxrefno', 'PX-SEC-MST-001');
        $this->assertNotNull($matched);
        $this->assertEquals('CASE-2026-0001', $matched['casecode']);
        $this->assertEquals('PIN-SEC-MST-001', $matched['pincode']);
    }

    /**
     * Detailed Comment: Verify authenticated doctor can create, fetch, edit, and delete their own clinic schedules.
     */
    public function test_doctor_can_manage_schedules_crud(): void
    {
        $doctor = DoctorModel::first();
        $this->assertNotNull($doctor);

        // 1. Create a schedule
        $createResponse = $this->actingAs($doctor, 'doctor')->post('/api/doctor/create_schedule', [
            'day' => 'Wednesday',
            'start' => '09:00:00',
            'end' => '13:00:00',
        ]);

        $createResponse->assertStatus(200);
        $createResponse->assertJson(['success' => true]);
        $schedrefno = $createResponse->json('schedule.schedrefno');
        $this->assertNotEmpty($schedrefno);

        $this->assertDatabaseHas('docschedules', [
            'schedrefno' => $schedrefno,
            'docrefno' => $doctor->docrefno,
            'day' => 'Wednesday'
        ]);

        // 2. Fetch the created schedule by refno
        $fetchResponse = $this->actingAs($doctor, 'doctor')->post('/api/doctor/fetch_schedule_refno', [
            'schedrefno' => $schedrefno
        ]);
        $fetchResponse->assertStatus(200);
        $fetchResponse->assertJson(['success' => true]);
        $this->assertEquals('Wednesday', $fetchResponse->json('sched.day'));

        // 3. Edit the schedule
        $editResponse = $this->actingAs($doctor, 'doctor')->post('/api/doctor/edit_schedule', [
            'schedrefno' => $schedrefno,
            'day' => 'Wednesday',
            'start' => '10:00:00',
            'end' => '14:00:00',
        ]);
        $editResponse->assertStatus(200);
        $editResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('docschedules', [
            'schedrefno' => $schedrefno,
            'start' => '10:00:00',
            'end' => '14:00:00'
        ]);

        // 4. Delete the schedule
        $deleteResponse = $this->actingAs($doctor, 'doctor')->post('/api/doctor/delete_schedule', [
            'schedrefno' => $schedrefno
        ]);
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);

        $this->assertDatabaseMissing('docschedules', [
            'schedrefno' => $schedrefno
        ]);
    }

    /**
     * Detailed Comment: Verify that a doctor cannot edit or delete another doctor's clinic schedule.
     */
    public function test_doctor_cannot_modify_other_doctor_schedule(): void
    {
        $admin = AdminModel::first();
        $doctor1 = DoctorModel::first();

        // Create second doctor
        $this->actingAs($admin, 'admin')->post('/api/add_doctor', [
            'docfname' => 'James',
            'doclname' => 'Wilson',
            'pass' => 'password123',
            'status' => 'ACTIVE'
        ]);
        $doctor2 = DoctorModel::where('doclname', 'Wilson')->first();
        $this->assertNotNull($doctor2);

        // Doctor 1 creates a schedule
        $createResponse = $this->actingAs($doctor1, 'doctor')->post('/api/doctor/create_schedule', [
            'day' => 'Friday',
            'start' => '08:00:00',
            'end' => '12:00:00',
        ]);
        $schedrefno = $createResponse->json('schedule.schedrefno');

        // Doctor 2 attempts to edit Doctor 1's schedule -> must fail 404
        $unauthEditResponse = $this->actingAs($doctor2, 'doctor')->post('/api/doctor/edit_schedule', [
            'schedrefno' => $schedrefno,
            'day' => 'Friday',
            'start' => '13:00:00',
            'end' => '17:00:00',
        ]);
        $unauthEditResponse->assertStatus(404);

        // Doctor 2 attempts to delete Doctor 1's schedule -> must fail 404
        $unauthDeleteResponse = $this->actingAs($doctor2, 'doctor')->post('/api/doctor/delete_schedule', [
            'schedrefno' => $schedrefno
        ]);
        $unauthDeleteResponse->assertStatus(404);

        // Schedule must still exist unchanged
        $this->assertDatabaseHas('docschedules', [
            'schedrefno' => $schedrefno,
            'docrefno' => $doctor1->docrefno,
            'start' => '08:00:00'
        ]);
    }
}
