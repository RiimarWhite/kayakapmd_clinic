<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationModel;
use App\Models\DoctorsProfileModel;
use App\Models\HMOModel;
use App\Models\SettlementsModel;
use App\Models\Stocks\StocksLedgerModel;
use App\Models\Stocks\StocksListingModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Feature tests verifying consultation charges, medicine inclusion,
 * settlements lifecycle, and unscheduled queue exclusion of scheduled patients.
 */
class ConsultationChargesAndSettlementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Verify unscheduled queue endpoint excludes patients who have a schedule.
     */
    public function test_unscheduled_patients_table_excludes_scheduled_patients(): void
    {
        $admin = AdminModel::first();

        // Patient 1: Unscheduled without date
        ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_UNSCHED_1',
            'caseno' => 'CASE_001',
            'pincode' => 'PIN_001',
            'patientname' => 'UNSCHED, PATIENT ONE',
            'finadiagnosis' => '',
            'pxrefno' => 'PTN_UNSCHED_001',
            'status' => 'UNSCHEDULED',
            'consultation_date' => null
        ]);

        // Patient 2: Has a scheduled consultation
        ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_SCHED_2',
            'caseno' => 'CASE_002',
            'pincode' => 'PIN_002',
            'patientname' => 'SCHED, PATIENT TWO',
            'finadiagnosis' => '',
            'pxrefno' => 'PTN_SCHED_002',
            'status' => 'WAITING',
            'consultation_date' => '2026-09-25 09:00:00'
        ]);

        // Patient 2 also has an old unscheduled record
        ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_UNSCHED_2_OLD',
            'caseno' => 'CASE_003',
            'pincode' => 'PIN_002',
            'patientname' => 'SCHED, PATIENT TWO',
            'finadiagnosis' => '',
            'pxrefno' => 'PTN_SCHED_002',
            'status' => 'UNSCHEDULED',
            'consultation_date' => null
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson('/api/fetch_unscheduled_patients', [
            'start' => 0,
            'length' => 10
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        // Patient 1 should be present
        $pxrefnos = collect($data)->pluck('pxrefno')->all();
        $this->assertContains('PTN_UNSCHED_001', $pxrefnos);

        // Patient 2 (who has a schedule) must NOT be present
        $this->assertNotContains('PTN_SCHED_002', $pxrefnos);
    }

    /**
     * Detailed Comment: Verify patient charges include prescribed medicines (DRUGS AND MEDS) with prices.
     */
    public function test_patient_charges_include_prescribed_drugs_and_meds(): void
    {
        $admin = AdminModel::first();

        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CON_TEST_RX_CHARGES',
            'caseno' => 'CASE_RX_001',
            'pincode' => 'PIN_RX_001',
            'patientname' => 'TEST, PATIENT RX',
            'finadiagnosis' => '',
            'pxrefno' => 'PTN_TEST_RX',
            'status' => 'IN_CONSULTATION',
            'consultation_date' => now()
        ]);

        $medListing = StocksListingModel::create([
            'prod_itemdscr' => 'Amoxicillin 500mg Capsule',
            'item_grouping' => 'DRUGS AND MEDS',
            'price_regular' => 15.50,
            'is_inventory' => 0,
            'qty' => 100
        ]);

        // Add medicine
        $addResponse = $this->actingAs($admin, 'admin')->postJson('/api/add_medicine', [
            'consultationrefno' => $consultation->consultationrefno,
            'prodcode' => $medListing->prodcode,
            'qty' => 3
        ]);

        $addResponse->assertStatus(200);
        $addResponse->assertJson(['success' => true]);

        // Fetch patient charges
        $chargesResponse = $this->actingAs($admin, 'admin')->postJson('/api/fetch_patient_charges', [
            'consultationrefno' => $consultation->consultationrefno
        ]);

        $chargesResponse->assertStatus(200);
        $charges = $chargesResponse->json('charges');

        $this->assertCount(1, $charges);
        $this->assertEquals('Amoxicillin 500mg Capsule', $charges[0]['item_dscr']);
        $this->assertEquals('15.50', $charges[0]['cost_ave']);
        $this->assertEquals('46.50', $charges[0]['totalamt']);
    }

    /**
     * Detailed Comment: Verify settlements save, fetch, and HMO fallback functionality.
     */
    public function test_settlements_save_fetch_and_hmo_population(): void
    {
        $admin = AdminModel::first();

        HMOModel::insert([
            'id' => 1,
            'dw_clientcode' => '122377',
            'hmocode' => 'HMO_TEST_001',
            'hmoname' => 'MaxiCare Health',
            'hmotype' => 'HMO'
        ]);

        // Fetch HMO
        $hmoResponse = $this->actingAs($admin, 'admin')->postJson('/api/fetch_hmo');
        $hmoResponse->assertStatus(200);
        $this->assertNotEmpty($hmoResponse->json('hmo'));

        // Save settlements
        $saveResponse = $this->actingAs($admin, 'admin')->postJson('/api/save_settlements', [
            'sett_consultationrefno' => 'CON_TEST_SETT_001',
            'total' => '500.00',
            'cash' => '300.00',
            'cta' => '200.00',
            'card_type' => 'cc',
            'hmo' => '0.00',
            'hmo_type' => null
        ]);

        $saveResponse->assertStatus(200);
        $saveResponse->assertJson(['success' => true]);

        // Fetch settlements
        $fetchResponse = $this->actingAs($admin, 'admin')->postJson('/api/fetch_settlements', [
            'consultationrefno' => 'CON_TEST_SETT_001'
        ]);

        $fetchResponse->assertStatus(200);
        $fetchResponse->assertJson(['success' => true]);
        $record = $fetchResponse->json('record');
        $this->assertEquals('500.00', $record['net_total']);
        $this->assertEquals('300.00', $record['cash']);
        $this->assertEquals('200.00', $record['cta']);
        $this->assertEquals('cc', $record['cta_type']);
    }
}
