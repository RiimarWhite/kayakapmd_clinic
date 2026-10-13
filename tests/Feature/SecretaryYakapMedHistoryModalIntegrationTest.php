<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\SecretaryModel;
use App\Models\ConsultationModel;
use App\Models\DoctorsProfileModel;
use App\Models\SecretaryDoctorsModel;
use App\Models\Libraries\MDiseaseLibModel;
use App\Models\ProfileModel;
use App\Models\Profiles\ProfileMedHistModel;
use App\Models\Profiles\ProfileMhSpecificModel;
use App\Models\Profiles\ProfileSurgHistModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretaryYakapMedHistoryModalIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $secretary;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);

        // Ensure disease library items exist for in-memory SQLite testing
        if (MDiseaseLibModel::count() === 0) {
            MDiseaseLibModel::create([
                'mdisease_code' => '001',
                'mdisease_desc' => 'Allergy',
                'sort_no' => 1,
                'lib_stat' => 1,
            ]);
            MDiseaseLibModel::create([
                'mdisease_code' => '011',
                'mdisease_desc' => 'Hypertension',
                'sort_no' => 11,
                'lib_stat' => 1,
            ]);
            MDiseaseLibModel::create([
                'mdisease_code' => '999',
                'mdisease_desc' => 'None',
                'sort_no' => 999,
                'lib_stat' => 1,
            ]);
        }

        $this->secretary = SecretaryModel::first();
        $this->admin = AdminModel::first();

        $doctor = DoctorsProfileModel::first();
        if ($doctor && $this->secretary) {
            SecretaryDoctorsModel::create([
                'secrefno' => $this->secretary->secrefno,
                'docrefno' => $doctor->docrefno,
                'recordedby' => 'system',
                'recordeddate' => now(),
                'active' => 1,
            ]);
        }
    }

    /**
     * Test Secretary and Admin can load queue page with $mDiseaseLibrary populated and modal included.
     */
    public function test_queue_page_renders_with_disease_library_and_yakap_modal()
    {
        // 1. As Secretary
        $responseSec = $this->actingAs($this->secretary, 'secretary')
            ->get(route('secretary.queue'));

        $responseSec->assertStatus(200);
        $responseSec->assertViewHas('mDiseaseLibrary');
        $responseSec->assertSee('yakap_medhistory_modal');
        $responseSec->assertSee('btn_yakap_medhistory_modal');

        // 2. As Admin
        $responseAdmin = $this->actingAs($this->admin, 'admin')
            ->get(route('secretary.queue'));

        $responseAdmin->assertStatus(200);
        $responseAdmin->assertViewHas('mDiseaseLibrary');
        $responseAdmin->assertSee('yakap_medhistory_modal');
        $responseAdmin->assertSee('btn_yakap_medhistory_modal');
    }

    /**
     * Test fetch_patient_yakap_medhistory endpoint returns correct structure.
     */
    public function test_fetch_patient_yakap_medhistory_endpoint()
    {
        $pxrefno = 'PX_YAKAP_TEST_' . uniqid();
        $pincode = 'PIN_' . uniqid();
        $consultRef = 'CON_YAKAP_TEST_' . uniqid();

        $consult = ConsultationModel::create([
            'consultationrefno' => $consultRef,
            'pxrefno' => $pxrefno,
            'pincode' => $pincode,
            'patientname' => 'Santos, Maria Test',
            'pxfirstname' => 'Maria',
            'pxlastname' => 'Santos',
            'consultation_date' => now()->toDateString(),
            'status' => 'WAITING',
        ]);

        $response = $this->actingAs($this->secretary, 'secretary')
            ->postJson('/api/fetch_patient_yakap_medhistory', [
                'pxrefno' => $pxrefno,
                'pincode' => $pincode,
                'consultationrefno' => $consultRef,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'profile',
                'transNo',
                'medHist',
                'mhSpecific',
                'surgHist',
            ]);
    }

    /**
     * Test saving Yakap medical conditions and surgical operations.
     */
    public function test_save_patient_yakap_medhistory_persists_records()
    {
        $pxrefno = 'PX_SAVE_TEST_' . uniqid();
        $pincode = 'PIN_SAVE_' . uniqid();
        $consultRef = 'CON_SAVE_TEST_' . uniqid();

        $consult = ConsultationModel::create([
            'consultationrefno' => $consultRef,
            'pxrefno' => $pxrefno,
            'pincode' => $pincode,
            'patientname' => 'Cruz, Juan Test',
            'pxfirstname' => 'Juan',
            'pxlastname' => 'Cruz',
            'consultation_date' => now()->toDateString(),
            'status' => 'WAITING',
        ]);

        $payload = [
            'pxrefno' => $pxrefno,
            'pincode' => $pincode,
            'consultationrefno' => $consultRef,
            'patientname' => 'Cruz, Juan Test',
            'chkMedHistDiseases' => [
                '001' => [
                    'selected' => '001',
                    'specify' => 'Severe shrimp allergy',
                ],
                '011' => [
                    'selected' => '011',
                    'specify' => 'Diagnosed 2021',
                ],
            ],
            'surgicalHistory' => [
                [
                    'operation' => 'Appendectomy',
                    'date' => '2019-05-12',
                ],
                [
                    'operation' => 'Knee Arthroscopy',
                    'date' => '2022-11-20',
                ],
            ],
        ];

        $response = $this->actingAs($this->secretary, 'secretary')
            ->postJson('/api/save_patient_yakap_medhistory', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Yakap Medical History and Profiling successfully saved.',
            ]);

        $transNo = $response->json('transNo');
        $this->assertNotEmpty($transNo);

        // Verify profile header was created in dd_profile_1
        $this->assertDatabaseHas('dd_profile_1', [
            'dTransNo' => $transNo,
            'px_pin' => $pincode,
        ]);

        // Verify medical conditions saved in dd_profile_medhist
        $this->assertDatabaseHas('dd_profile_medhist', [
            'p_TransNo' => $transNo,
            'dMdiseaseCode' => '001',
        ]);
        $this->assertDatabaseHas('dd_profile_medhist', [
            'p_TransNo' => $transNo,
            'dMdiseaseCode' => '011',
        ]);

        // Verify specific descriptions saved in dd_profile_mhspecific
        $this->assertDatabaseHas('dd_profile_mhspecific', [
            'p_TransNo' => $transNo,
            'dMdiseaseCode' => '001',
            'dSpecificDesc' => 'Severe shrimp allergy',
        ]);
        $this->assertDatabaseHas('dd_profile_mhspecific', [
            'p_TransNo' => $transNo,
            'dMdiseaseCode' => '011',
            'dSpecificDesc' => 'Diagnosed 2021',
        ]);

        // Verify surgical history saved in dd_profile_surghist
        $this->assertDatabaseHas('dd_profile_surghist', [
            'p_TransNo' => $transNo,
            'dSurgDesc' => 'Appendectomy',
            'dSurgDate' => '2019-05-12',
        ]);
        $this->assertDatabaseHas('dd_profile_surghist', [
            'p_TransNo' => $transNo,
            'dSurgDesc' => 'Knee Arthroscopy',
            'dSurgDate' => '2022-11-20',
        ]);
    }
}
