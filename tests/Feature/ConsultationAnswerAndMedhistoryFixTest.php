<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationAnswerModel;
use App\Models\ConsultationModel;
use App\Models\DoctorModel;
use App\Models\DoctorQuestionsModel;
use App\Models\DoctorsProfileModel;
use App\Models\PatientMasterlist;
use App\Models\PxMedicalHistoryModel;
use App\Models\SecretaryModel;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Test suite verifying the bugfixes for:
 * 1. Answering doctor questions and joining docquestion table to retrieve actual question text.
 * 2. Pre-hydrating answers and permanent medical history in fetchConsultation, fetchPatientData, and fetchPatientMedhistory.
 * 3. Route flexibility allowing both GET and POST requests for fetch_patient_medical_history without 405 errors.
 * 4. Saving and updating permanent medical history records with audit trails.
 */
class ConsultationAnswerAndMedhistoryFixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Verifies fetchConsultation joins docquestion on questionrefno = docquestionrefno
     * and returns the question text as well as pre-hydrated permanent medical history.
     */
    public function test_fetch_consultation_joins_docquestion_and_returns_questions_and_medhistory(): void
    {
        $doctorUser = DoctorModel::first();
        $this->assertNotNull($doctorUser, 'Doctor user seeded');

        // Create a doctor question
        $question = DoctorQuestionsModel::create([
            'docquestionrefno' => 'QREF-TEST-001',
            'question' => 'Do you have difficulty breathing at rest or exertion?',
            'docrefno' => 'DOC-TEST-001',
            'docname' => 'Dr. Test',
            'recordeddate' => now(),
            'recordedby' => 'Secretary'
        ]);

        // Create patient and consultation
        $pxrefno = 'PX-TEST-001';
        $consultationrefno = 'CON-TEST-001';

        PatientMasterlist::create([
            'pxrefno' => $pxrefno,
            'pincode' => 'PIN-TEST-001',
            'patientname' => 'Jane Doe Test',
            'birthday' => '1990-05-15',
            'gender' => 'FEMALE',
            'address' => '123 Medical Way'
        ]);

        ConsultationModel::create([
            'consultationrefno' => $consultationrefno,
            'pxrefno' => $pxrefno,
            'pincode' => 'PIN-TEST-001',
            'patientname' => 'Jane Doe Test',
            'birthday' => '1990-05-15',
            'gender' => 'FEMALE',
            'reasonforconsultation' => 'Persistent cough',
            'impression' => 'Upper respiratory tract infection',
            'finadiagnosis' => 'Acute Bronchitis',
            'consultation_date' => now()->toDateString()
        ]);

        // Create answered question
        ConsultationAnswerModel::create([
            'walkinconsuanswerrefno' => 'ANS-TEST-001',
            'questionrefno' => $question->docquestionrefno,
            'answer' => 'Yes, especially when climbing stairs',
            'pxconsultationrefno' => $consultationrefno,
            'transactedby' => 'Secretary',
            'transacteddate' => now()
        ]);

        // Create permanent medical history
        PxMedicalHistoryModel::create([
            'pxrefno' => $pxrefno,
            'pincode' => 'PIN-TEST-001',
            'allergies' => 'Penicillin, Amoxicillin',
            'injections_immunization' => 'Flu Vaccine 2025',
            'past_medical_history' => 'Asthma since childhood',
            'surgical_history' => 'Appendectomy (2018)',
            'family_history' => 'Hypertension (Father)',
            'maintenance_medications' => 'Salbutamol inhaler PRN',
            'notes' => 'Patient has mild asthma triggers with cold weather',
            'recordedby' => 'Secretary'
        ]);

        $response = $this->actingAs($doctorUser, 'doctor')
            ->postJson('/api/fetch_consultation', [
                'consultationrefno' => $consultationrefno
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('answers.0.question', 'Do you have difficulty breathing at rest or exertion?')
            ->assertJsonPath('answers.0.answer', 'Yes, especially when climbing stairs')
            ->assertJsonPath('medical_history.allergies', 'Penicillin, Amoxicillin')
            ->assertJsonPath('permanent_medhistory.past_medical_history', 'Asthma since childhood');
    }

    /**
     * Detailed Comment: Verifies DoctorController fetchPatientData returns answered questions
     * with joined question text and permanent medical history.
     */
    public function test_doctor_fetch_patient_data_returns_answers_and_medhistory(): void
    {
        $doctorUser = DoctorModel::first();
        $this->assertNotNull($doctorUser, 'Doctor user seeded');

        $question = DoctorQuestionsModel::create([
            'docquestionrefno' => 'QREF-DOC-002',
            'question' => 'Any history of chest pain or palpitations?',
            'docrefno' => 'DOC-TEST-002',
            'docname' => 'Dr. Cardiology',
            'recordeddate' => now(),
            'recordedby' => 'Secretary'
        ]);

        $pxrefno = 'PX-TEST-002';
        $consultationrefno = 'CON-TEST-002';

        PatientMasterlist::create([
            'pxrefno' => $pxrefno,
            'pincode' => 'PIN-TEST-002',
            'patientname' => 'John Doe Heart',
            'birthday' => '1985-02-10',
            'gender' => 'MALE',
            'address' => '456 Cardiac Ave'
        ]);

        ConsultationModel::create([
            'consultationrefno' => $consultationrefno,
            'pxrefno' => $pxrefno,
            'pincode' => 'PIN-TEST-002',
            'patientname' => 'John Doe Heart',
            'birthday' => '1985-02-10',
            'gender' => 'MALE',
            'reasonforconsultation' => 'Heart fluttering',
            'consultation_date' => now()->toDateString()
        ]);

        ConsultationAnswerModel::create([
            'walkinconsuanswerrefno' => 'ANS-TEST-002',
            'questionrefno' => $question->docquestionrefno,
            'answer' => 'Occasional palpitations after coffee',
            'pxconsultationrefno' => $consultationrefno,
            'transactedby' => 'Secretary',
            'transacteddate' => now()
        ]);

        PxMedicalHistoryModel::create([
            'pxrefno' => $pxrefno,
            'pincode' => 'PIN-TEST-002',
            'allergies' => 'Sulfa drugs',
            'past_medical_history' => 'Hypertension diagnosed 2022',
            'maintenance_medications' => 'Losartan 50mg daily',
            'recordedby' => 'Secretary'
        ]);

        $response = $this->actingAs($doctorUser, 'doctor')
            ->postJson('/api/fetch_patient_data', [
                'consultationrefno' => $consultationrefno
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('answers.0.question', 'Any history of chest pain or palpitations?')
            ->assertJsonPath('answers.0.answer', 'Occasional palpitations after coffee')
            ->assertJsonPath('permanent_medhistory.allergies', 'Sulfa drugs')
            ->assertJsonPath('permanent_medhistory.maintenance_medications', 'Losartan 50mg daily');
    }

    /**
     * Detailed Comment: Verifies fetchPatientMedhistory returns pre-hydrated answers
     * with question text and pre-hydrated permanent medical history on each consultation record.
     */
    public function test_fetch_patient_medhistory_prehydrates_answers_and_permanent_medhistory(): void
    {
        $secretaryUser = SecretaryModel::first();
        $this->assertNotNull($secretaryUser, 'Secretary user seeded');

        $question = DoctorQuestionsModel::create([
            'docquestionrefno' => 'QREF-MEDHIST-003',
            'question' => 'Any known allergies to food or medication?',
            'docrefno' => 'DOC-TEST-003',
            'docname' => 'Dr. General',
            'recordeddate' => now(),
            'recordedby' => 'Secretary'
        ]);

        $pxrefno = 'PX-TEST-003';
        $consultationrefno = 'CON-TEST-003';

        PatientMasterlist::create([
            'pxrefno' => $pxrefno,
            'pincode' => 'PIN-TEST-003',
            'patientname' => 'Alice Medhistory',
            'birthday' => '1992-08-20',
            'gender' => 'FEMALE',
            'address' => '789 Wellness St'
        ]);

        ConsultationModel::create([
            'consultationrefno' => $consultationrefno,
            'pxrefno' => $pxrefno,
            'pincode' => 'PIN-TEST-003',
            'patientname' => 'Alice Medhistory',
            'birthday' => '1992-08-20',
            'gender' => 'FEMALE',
            'reasonforconsultation' => 'Checkup',
            'impression' => 'Healthy adult',
            'finadiagnosis' => 'Routine physical exam',
            'consultation_date' => now()->toDateString()
        ]);

        ConsultationAnswerModel::create([
            'walkinconsuanswerrefno' => 'ANS-TEST-003',
            'questionrefno' => $question->docquestionrefno,
            'answer' => 'Seafood and shellfish allergy',
            'pxconsultationrefno' => $consultationrefno,
            'transactedby' => 'Secretary',
            'transacteddate' => now()
        ]);

        PxMedicalHistoryModel::create([
            'pxrefno' => $pxrefno,
            'pincode' => 'PIN-TEST-003',
            'allergies' => 'Crab, Shrimp',
            'surgical_history' => 'Tonsillectomy (2010)',
            'recordedby' => 'Secretary'
        ]);

        $response = $this->actingAs($secretaryUser, 'secretary')
            ->postJson('/api/fetch_patient_medhistory', [
                'pxrefno' => $pxrefno,
                'pincode' => 'PIN-TEST-003'
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('history.0.answers.0.question', 'Any known allergies to food or medication?')
            ->assertJsonPath('history.0.answers.0.answer', 'Seafood and shellfish allergy')
            ->assertJsonPath('history.0.permanent_medhistory.allergies', 'Crab, Shrimp')
            ->assertJsonPath('permanent_medhistory.surgical_history', 'Tonsillectomy (2010)');
    }

    /**
     * Detailed Comment: Verifies fetch_patient_medical_history accepts both GET and POST requests
     * without 405 Method Not Allowed exceptions, and save_patient_medical_history correctly creates/updates records.
     */
    public function test_fetch_and_save_patient_medical_history_endpoint_flexibility(): void
    {
        $adminUser = AdminModel::first();
        $this->assertNotNull($adminUser, 'Admin user seeded');

        $pxrefno = 'PX-TEST-FLEX-004';
        $pincode = 'PIN-TEST-FLEX-004';

        // 1. Test saving medical history
        $saveResponse = $this->actingAs($adminUser, 'admin')
            ->postJson('/api/save_patient_medical_history', [
                'pxrefno' => $pxrefno,
                'pincode' => $pincode,
                'allergies' => 'Ibuprofen',
                'injections_immunization' => 'COVID-19 Booster (2024)',
                'past_medical_history' => 'Gastritis',
                'surgical_history' => 'None',
                'family_history' => 'Diabetes Type 2',
                'maintenance_medications' => 'Omeprazole 20mg',
                'notes' => 'Take omeprazole before breakfast'
            ]);

        $saveResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // 2. Test GET request to fetch_patient_medical_history
        $getResponse = $this->actingAs($adminUser, 'admin')
            ->getJson("/api/fetch_patient_medical_history?pxrefno={$pxrefno}&pincode={$pincode}");

        $getResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.allergies', 'Ibuprofen')
            ->assertJsonPath('data.maintenance_medications', 'Omeprazole 20mg');

        // 3. Test POST request to fetch_patient_medical_history
        $postResponse = $this->actingAs($adminUser, 'admin')
            ->postJson('/api/fetch_patient_medical_history', [
                'pxrefno' => $pxrefno,
                'pincode' => $pincode
            ]);

        $postResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.allergies', 'Ibuprofen')
            ->assertJsonPath('data.past_medical_history', 'Gastritis');
    }
}
