<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationModel;
use App\Models\DoctorsProfileModel;
use App\Models\SecretaryModel;
use App\Models\Stocks\StocksLedgerModel;
use App\Models\Stocks\StocksListingModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Feature tests verifying the new queue draggable reordering,
 * queue number sequential calculation, consultation history detailed payload,
 * doctor consultation fee auto-calculation from dashboard rate, and tiered pricing per patient type.
 */
class QueueDoctorConsultationAndStocksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Verifies that /api/reorder_queue updates queueno in sequential order for given consultations.
     */
    public function test_reorder_queue_persists_new_queue_numbers(): void
    {
        $admin = AdminModel::first();

        $c1 = ConsultationModel::create([
            'consultationrefno' => 'CON_REORDER_001',
            'caseno' => 'CASE_R1',
            'pincode' => 'PIN_R1',
            'patientname' => 'PATIENT ALPHA',
            'finadiagnosis' => '',
            'status' => 'WAITING',
            'queueno' => '001',
            'consultation_date' => '2026-10-02 09:00:00'
        ]);

        $c2 = ConsultationModel::create([
            'consultationrefno' => 'CON_REORDER_002',
            'caseno' => 'CASE_R2',
            'pincode' => 'PIN_R2',
            'patientname' => 'PATIENT BETA',
            'finadiagnosis' => '',
            'status' => 'WAITING',
            'queueno' => '002',
            'consultation_date' => '2026-10-02 09:30:00'
        ]);

        // Reverse the order
        $response = $this->actingAs($admin, 'admin')->postJson('/api/reorder_queue', [
            'queue' => [
                ['consultationrefno' => 'CON_REORDER_002', 'queueno' => 1],
                ['consultationrefno' => 'CON_REORDER_001', 'queueno' => 2],
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals('001', $c2->fresh()->queueno);
        $this->assertEquals('002', $c1->fresh()->queueno);
    }

    /**
     * Detailed Comment: Verifies saveConsultation computes next queue number and saves classification.
     */
    public function test_save_consultation_calculates_queue_and_stores_classification(): void
    {
        $admin = AdminModel::first();
        $doctor = DoctorsProfileModel::first();

        // Existing patient on the same day
        ConsultationModel::create([
            'consultationrefno' => 'CON_EXISTING_Q',
            'docrefno' => $doctor->docrefno,
            'caseno' => 'CASE_EX',
            'pincode' => 'PIN_EX',
            'patientname' => 'EXISTING PATIENT',
            'finadiagnosis' => '',
            'status' => 'WAITING',
            'queueno' => '005',
            'consultation_date' => '2026-10-02 08:00:00'
        ]);

        $response = $this->actingAs($admin, 'admin')->post('/api/save_patient_consultation', [
            'docrefno' => $doctor->docrefno,
            'pxfname' => 'John',
            'pxlname' => 'Doe',
            'pxsex' => 'male',
            'sched_date' => '2026-10-02',
            'sched_time' => '10:00:00',
            'patient_type' => 'phic',
            'pincode' => 'PIN_NEW_001'
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);

        $saved = ConsultationModel::where('consultationrefno', $data['consultationrefno'])->first();
        $this->assertNotNull($saved);
        $this->assertEquals('006', $saved->queueno);
        $this->assertEquals('PHIC', $saved->classification);
    }

    /**
     * Detailed Comment: Verifies fetch_patient_medhistory returns comprehensive history records
     * including embedded rx_items, diagnostic_items, and stocks_ledger charges.
     */
    public function test_fetch_patient_medhistory_returns_detailed_consultation_modal_data(): void
    {
        $admin = AdminModel::first();

        $consult = ConsultationModel::create([
            'consultationrefno' => 'CON_DETAILED_001',
            'caseno' => 'CASE_DET_1',
            'pincode' => 'PIN_DET_1',
            'pxrefno' => 'PX_DET_1',
            'patientname' => 'DETAILED PATIENT',
            'reasonforconsultation' => 'Persistent cough and fever',
            'impression' => 'Acute Bronchitis',
            'finadiagnosis' => 'J20.9',
            'classification' => 'HMO',
            'status' => 'COMPLETED',
            'weight' => '65',
            'wunit' => 'kg',
            'height' => '170',
            'hunit' => 'cm',
            'temp' => '38.5',
            'tempunit' => 'C',
            'bpnumerator' => '120',
            'bpdenominator' => '80',
            'consultation_date' => '2026-10-01 14:00:00'
        ]);

        // Add a charge in stocks_ledger
        StocksLedgerModel::create([
            'px_consultcode_cn' => 'CON_DETAILED_001',
            'dw_clientcode' => 'HO1',
            'transactiontype' => 'CHARGES',
            'px_pin' => 'PX_DET_1',
            'patient_name' => 'DETAILED PATIENT',
            'prodcode' => 'PF_DR',
            'item_dscr' => 'Professional Fee',
            'item_grouping' => 'PROFESSIONAL FEE',
            'cost_ave' => 500.00,
            'retails' => 500.00,
            'qty' => 1,
            'totalamt' => 500.00,
            'remarks' => 'Doctor Fee',
            'updatedby' => 'admin',
            'updated' => now()
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson('/api/fetch_patient_medhistory', [
            'pincode' => 'PIN_DET_1',
            'pxrefno' => 'PX_DET_1',
            'consultationrefno' => 'CON_DETAILED_001'
        ]);

        $response->assertStatus(200);
        $resData = $response->json();
        $this->assertNotEmpty($resData['medhistory']);

        $first = $resData['medhistory'][0];
        $this->assertEquals('CON_DETAILED_001', $first['consultationrefno']);
        $this->assertEquals('Persistent cough and fever', $first['reasonforconsultation']);
        $this->assertEquals('HMO', $first['classification']);
        $this->assertEquals('120', $first['bpnumerator']);
        $this->assertCount(1, $first['charges']);
        $this->assertEquals(500.00, floatval($first['charges_total']));
    }

    /**
     * Detailed Comment: Verifies get_hmo_price returns tiered prices by patient type.
     */
    public function test_get_hmo_price_resolves_tier_price_by_patient_type(): void
    {
        $admin = AdminModel::first();

        $item = StocksListingModel::create([
            'prodcode' => 'TEST_TIER_ITEM',
            'item_dscr' => 'Test Procedure',
            'cost_ave' => 200.00,
            'price_regular' => 500.00,
            'price_phic' => 400.00,
            'price_hmo' => 350.00,
            'price_others' => 450.00,
            'stat' => 'A'
        ]);

        // Test PHIC tier
        $resPhic = $this->actingAs($admin, 'admin')->postJson('/api/get_hmo_price', [
            'prodcode' => 'TEST_TIER_ITEM',
            'patient_type' => 'phic'
        ]);
        $resPhic->assertStatus(200);
        $this->assertEquals(400.00, floatval($resPhic->json('price')));

        // Test HMO tier
        $resHmo = $this->actingAs($admin, 'admin')->postJson('/api/get_hmo_price', [
            'prodcode' => 'TEST_TIER_ITEM',
            'patient_type' => 'hmo'
        ]);
        $resHmo->assertStatus(200);
        $this->assertEquals(350.00, floatval($resHmo->json('price')));

        // Test Regular fallback
        $resReg = $this->actingAs($admin, 'admin')->postJson('/api/get_hmo_price', [
            'prodcode' => 'TEST_TIER_ITEM',
            'patient_type' => 'regular'
        ]);
        $resReg->assertStatus(200);
        $this->assertEquals(500.00, floatval($resReg->json('price')));
    }
}
