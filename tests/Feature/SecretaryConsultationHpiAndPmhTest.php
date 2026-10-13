<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationModel;
use App\Models\DoctorsProfileModel;
use App\Models\SecretaryDoctorsModel;
use App\Models\SecretaryModel;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Feature test suite verifying:
 * 1. Secretary and Admin console renders the updated Patient Medical History header,
 *    the col-12 col-sm-4 Patient Classification layout, and the new narrative fields
 *    ("Brief History of Present Illness" and "Pertinent Past Medical History").
 * 2. Successful creation and database persistence of consultations with HPI and PMH narratives.
 * 3. Successful update of existing consultation records including HPI and PMH narrative fields.
 * 4. Retrieval of HPI and PMH narratives via fetch_patient_medhistory for details modal display.
 */
class SecretaryConsultationHpiAndPmhTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Verifies secretary queue page contains the updated header,
     * adjusted patient classification width, and new narrative textboxes.
     */
    public function test_secretary_queue_page_renders_new_fields_and_updated_header(): void
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

        // Verify updated tab header
        $response->assertSee('Patient Medical History');
        $response->assertDontSee('Permanent Patient Medical History');

        // Verify new narrative textboxes
        $response->assertSee('name="brief_history_present_illness"', false);
        $response->assertSee('name="pertinent_past_medhistory"', false);
        $response->assertSee('id="pxbriefhistory"', false);
        $response->assertSee('id="pxpastmedhistory"', false);

        // Verify Patient Classification width matching Weight grid
        $response->assertSee('Patient Classification');
        $response->assertSee('col-12 col-sm-4');
    }

    /**
     * Detailed Comment: Verifies saving new consultation persists brief_history_present_illness
     * and pertinent_past_medhistory to pxwalkinconsultation table.
     */
    public function test_save_patient_consultation_persists_hpi_and_pmh(): void
    {
        $secretary = SecretaryModel::first();
        $doctor = DoctorsProfileModel::first();

        $payload = [
            'docrefno' => $doctor->docrefno,
            'pxfname' => 'Juan',
            'pxmname' => 'Dela',
            'pxlname' => 'Cruz',
            'pxsuffix' => '',
            'pxsex' => 'male',
            'pxbday' => '1990-05-15',
            'pxage' => 35,
            'pxcellnumber' => '09123456789',
            'pxemail' => 'juan.cruz@example.com',
            'pxaddress' => '123 Rizal Street, Manila',
            'reason_for_consultation' => 'Persistent dry cough and mild fever',
            'brief_history_present_illness' => 'Patient experienced fever 3 days ago, accompanied by dry cough and malaise.',
            'pertinent_past_medhistory' => 'Known hypertensive for 2 years on Amlodipine 5mg. No previous hospitalizations.',
            'weight' => 68.5,
            'w_unit' => 'kgs',
            'height' => 170,
            'h_unit' => 'cm',
            'temperature' => 37.8,
            't_unit' => 'C',
            'respiratory_rate' => 18,
            'pulse_rate' => 78,
            'bp_numerator' => 120,
            'bp_denominator' => 80,
            'patient_classification' => 'MEDICAL',
            'sched_date' => now()->toDateString(),
            'sched_time' => '10:00:00',
        ];

        $response = $this->actingAs($secretary, 'secretary')
            ->postJson('/api/save_patient_consultation', $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('pxwalkinconsultation', [
            'pxfirstname' => 'Juan',
            'pxlastname' => 'Cruz',
            'reasonforconsultation' => 'Persistent dry cough and mild fever',
            'brief_history_present_illness' => 'Patient experienced fever 3 days ago, accompanied by dry cough and malaise.',
            'pertinent_past_medhistory' => 'Known hypertensive for 2 years on Amlodipine 5mg. No previous hospitalizations.',
        ]);
    }

    /**
     * Detailed Comment: Verifies updating existing consultation properly updates
     * brief_history_present_illness and pertinent_past_medhistory.
     */
    public function test_update_patient_consultation_updates_hpi_and_pmh(): void
    {
        $secretary = SecretaryModel::first();
        $doctor = DoctorsProfileModel::first();

        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CONTEST001',
            'pxrefno' => 'PXTEST001',
            'docrefno' => $doctor->docrefno,
            'docname' => $doctor->docname,
            'patientname' => 'Maria Santos',
            'pxfirstname' => 'Maria',
            'pxlastname' => 'Santos',
            'gender' => 'female',
            'birthday' => '1995-02-20',
            'age' => 30,
            'reasonforconsultation' => 'Headache and fatigue',
            'brief_history_present_illness' => 'Initial brief history of illness.',
            'pertinent_past_medhistory' => 'Initial past medical history.',
            'status' => 'PENDING',
            'consultation_date' => now(),
            'finadiagnosis' => '',
        ]);

        $updatePayload = [
            'pxconsultationrefno' => $consultation->consultationrefno,
            'pxrefno' => 'PXTEST001',
            'docrefno' => $doctor->docrefno,
            'pxfname' => 'Maria',
            'pxlname' => 'Santos',
            'pxsex' => 'female',
            'pxbday' => '1995-02-20',
            'pxage' => 30,
            'reason_for_consultation' => 'Updated: Throbbing migraine and photophobia',
            'brief_history_present_illness' => 'Updated HPI: Sudden onset throbbing headache since yesterday morning with nausea.',
            'pertinent_past_medhistory' => 'Updated PMH: History of migraine without aura. Allergic to NSAIDs.',
            'weight' => 54.0,
            'w_unit' => 'kgs',
            'height' => 158,
            'h_unit' => 'cm',
            'temperature' => 36.6,
            't_unit' => 'C',
            'patient_classification' => 'MEDICAL',
        ];

        $response = $this->actingAs($secretary, 'secretary')
            ->postJson('/api/update_patient_consultation', $updatePayload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('pxwalkinconsultation', [
            'consultationrefno' => 'CONTEST001',
            'reasonforconsultation' => 'Updated: Throbbing migraine and photophobia',
            'brief_history_present_illness' => 'Updated HPI: Sudden onset throbbing headache since yesterday morning with nausea.',
            'pertinent_past_medhistory' => 'Updated PMH: History of migraine without aura. Allergic to NSAIDs.',
        ]);
    }

    /**
     * Detailed Comment: Verifies fetch_patient_medhistory returns brief_history_present_illness
     * and pertinent_past_medhistory for consultation detail display across secretary/doctor.
     */
    public function test_fetch_patient_medhistory_returns_hpi_and_pmh(): void
    {
        $secretary = SecretaryModel::first();
        $doctor = DoctorsProfileModel::first();

        ConsultationModel::create([
            'consultationrefno' => 'CONTEST002',
            'pxrefno' => 'PXTEST002',
            'docrefno' => $doctor->docrefno,
            'docname' => $doctor->docname,
            'patientname' => 'Pedro Penduko',
            'pxfirstname' => 'Pedro',
            'pxlastname' => 'Penduko',
            'gender' => 'male',
            'birthday' => '1988-08-08',
            'age' => 37,
            'reasonforconsultation' => 'Abdominal discomfort',
            'brief_history_present_illness' => 'Epigastric pain postprandial for 1 week.',
            'pertinent_past_medhistory' => 'History of gastritis. Non-smoker.',
            'status' => 'COMPLETED',
            'consultation_date' => now(),
            'finadiagnosis' => 'Acute Gastritis',
        ]);

        $response = $this->actingAs($secretary, 'secretary')
            ->postJson('/api/fetch_patient_medhistory', ['pxrefno' => 'PXTEST002']);

        $response->assertStatus(200);
        $records = $response->json('history');
        $this->assertNotEmpty($records);
        $this->assertEquals('Epigastric pain postprandial for 1 week.', $records[0]['brief_history_present_illness']);
        $this->assertEquals('History of gastritis. Non-smoker.', $records[0]['pertinent_past_medhistory']);
    }
}
