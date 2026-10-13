<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationModel;
use App\Models\DoctorModel;
use App\Models\DoctorsProfileModel;
use App\Models\Stocks\StocksLedgerModel;
use App\Models\Stocks\StocksListingModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Detailed Comment: Feature test verifying the fixes for Doctor Consultation Modal Patient Charges:
 * 1. Accurate calculation and application of PHIC tier pricing on PHIC patients instead of defaulting to REGULAR pricing.
 * 2. Accurate calculation and synchronization of doctor Professional Fee (PF) with PHIC rate and autoAddVAT.
 * 3. Proper retrieval of HMO/PHIC/Others tier prices via /api/get_hmo_price.
 * 4. Reliable editing of fee amounts via /api/update_charge without SQL column exceptions.
 */
class DoctorConsultationChargesAndBadgeFixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Verifies that fetch_patient_charges calculates PHIC tier price for PHIC patients
     * and computes Professional Fee with auto-added VAT.
     */
    public function test_fetch_patient_charges_applies_phic_tier_price_and_computed_pf()
    {
        $uniqueSuffix = now()->format('mdYHis') . rand(100, 999);
        $consultRef = 'CONTEST' . $uniqueSuffix;
        $docRef = 'DOCTEST' . $uniqueSuffix;
        $prodCode = 'PRODTEST' . $uniqueSuffix;

        // Create Doctor Profile with PF rate = 500, PHIC rate = 400, autoAddVAT = 1 (12%)
        $docProfile = DoctorsProfileModel::create([
            'docrefno' => $docRef,
            'docname' => 'Dr. Test Specialist',
            'doclname' => 'Specialist',
            'docfname' => 'Test',
            'pfrate' => 500.00,
            'phicrate' => 400.00,
            'phicenable' => 1,
            'vatable' => 1,
            'autoAddVAT' => 1,
            'vatrate' => 12.00,
            'tax' => 10.00,
            'rodrate' => 10.00,
        ]);

        // Create doctor user for authentication
        $doctorUser = DoctorModel::create([
            'docrefno' => $docRef,
            'username' => 'dr_test_' . $uniqueSuffix,
            'password' => bcrypt('password123'),
            'firstname' => 'Test',
            'lastname' => 'Specialist',
            'department' => 'Internal Medicine'
        ]);

        // Create product in stocks_listing with different price tiers
        StocksListingModel::create([
            'prodcode' => $prodCode,
            'prod_itemdscr' => 'Test Medicine 500mg',
            'item_grouping' => 'DRUGS AND MEDS',
            'price_regular' => 20.00,
            'price_phic' => 12.00,
            'price_hmo' => 15.00,
            'price_others' => 18.00,
            'cost_ave' => 10.00,
            'is_inventory' => 0,
            'qty' => 100,
            'updated' => now()
        ]);

        // Create Consultation with modeofpayment = PHIC and classification = MEDICAL
        $consultation = ConsultationModel::create([
            'consultationrefno' => $consultRef,
            'patientname' => 'Juan Dela Cruz',
            'pxrefno' => 'PXTEST' . $uniqueSuffix,
            'docrefno' => $docRef,
            'classification' => 'MEDICAL',
            'modeofpayment' => 'PHIC',
            'status' => 'PENDING'
        ]);

        // Attach catalog charge item to consultation in stocks_ledger
        StocksLedgerModel::create([
            'transactiontype' => 'CHARGES',
            'px_consultcode_cn' => $consultRef,
            'prodcode' => $prodCode,
            'item_dscr' => 'Test Medicine 500mg',
            'qty' => 2,
            'cost_ave' => 12.00,
            'retails' => 12.00,
            'totalamt' => 24.00,
            'item_grouping' => 'DRUGS AND MEDS',
            'updated' => now()
        ]);

        // Call fetch_patient_charges as logged-in doctor
        $response = $this->actingAs($doctorUser, 'doctor')
            ->postJson('/api/fetch_patient_charges', [
                'consultationrefno' => $consultRef
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['charges']);

        $charges = collect($response->json('charges'));

        // 1. Verify Catalog item has PHIC tier applied
        $catalogItem = $charges->firstWhere('prodcode', $prodCode);
        $this->assertNotNull($catalogItem);
        $this->assertEquals('PHIC', $catalogItem['posted_tier']);
        $this->assertEquals('12.00', $catalogItem['posted_tier_price']);
        $this->assertEquals('20.00', $catalogItem['regular_price']);

        // 2. Verify Doctor Professional Fee was auto-populated with PHIC rate (400) + 12% VAT (48) = 448.00
        $pfItem = $charges->firstWhere('prodcode', 'PF');
        $this->assertNotNull($pfItem);
        $this->assertEquals('400.00', $pfItem['pf_rate']);
        $this->assertEquals('448.00', $pfItem['cost_ave']);
        $this->assertEquals('448.00', $pfItem['totalamt']);
        $this->assertEquals(1, $pfItem['auto_add_vat']);
    }

    /**
     * Detailed Comment: Verifies that get_hmo_price returns the PHIC tier price for PHIC consultations.
     */
    public function test_get_hmo_price_returns_phic_price_for_phic_consultation()
    {
        $uniqueSuffix = now()->format('mdYHis') . rand(100, 999);
        $consultRef = 'CONTEST' . $uniqueSuffix;
        $docRef = 'DOCTEST' . $uniqueSuffix;
        $prodCode = 'PRODTEST' . $uniqueSuffix;

        $doctorUser = DoctorModel::create([
            'docrefno' => $docRef,
            'username' => 'dr_test_' . $uniqueSuffix,
            'password' => bcrypt('password123'),
            'firstname' => 'Test',
            'lastname' => 'Specialist'
        ]);

        StocksListingModel::create([
            'prodcode' => $prodCode,
            'prod_itemdscr' => 'Diagnostic Test Item',
            'item_grouping' => 'DIAGNOSTIC',
            'price_regular' => 500.00,
            'price_phic' => 350.00,
            'price_hmo' => 400.00,
            'price_others' => 450.00,
            'cost_ave' => 200.00,
            'updated' => now()
        ]);

        ConsultationModel::create([
            'consultationrefno' => $consultRef,
            'patientname' => 'Maria Santos',
            'docrefno' => $docRef,
            'classification' => 'MEDICAL',
            'modeofpayment' => 'PHIC',
            'status' => 'PENDING'
        ]);

        $response = $this->actingAs($doctorUser, 'doctor')
            ->postJson('/api/get_hmo_price', [
                'consultationrefno' => $consultRef,
                'prodcode' => $prodCode
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'price' => '350.00'
            ]);
    }

    /**
     * Detailed Comment: Verifies that update_charge updates fee amount cleanly without SQL column errors.
     */
    public function test_update_charge_successfully_modifies_fee()
    {
        $uniqueSuffix = now()->format('mdYHis') . rand(100, 999);
        $consultRef = 'CONTEST' . $uniqueSuffix;
        $docRef = 'DOCTEST' . $uniqueSuffix;

        $doctorUser = DoctorModel::create([
            'docrefno' => $docRef,
            'username' => 'dr_test_' . $uniqueSuffix,
            'password' => bcrypt('password123'),
            'firstname' => 'Test',
            'lastname' => 'Specialist'
        ]);

        ConsultationModel::create([
            'consultationrefno' => $consultRef,
            'patientname' => 'Pedro Penduko',
            'docrefno' => $docRef,
            'classification' => 'MEDICAL',
            'modeofpayment' => 'PHIC',
            'status' => 'PENDING'
        ]);

        $ledger = StocksLedgerModel::create([
            'transactiontype' => 'CHARGES',
            'px_consultcode_cn' => $consultRef,
            'prodcode' => 'PF',
            'item_dscr' => 'Professional Fee',
            'qty' => 1,
            'cost_ave' => 400.00,
            'retails' => 400.00,
            'totalamt' => 400.00,
            'item_grouping' => 'PROFESSIONAL FEE',
            'updated' => now()
        ]);

        $response = $this->actingAs($doctorUser, 'doctor')
            ->postJson('/api/update_charge', [
                'consultationrefno' => $consultRef,
                'chargeid' => $ledger->id,
                'prodcode' => 'PF',
                'charge_fee' => 550.00,
                'charge_qty' => 1,
                'discount' => 0
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $ledger->refresh();
        $this->assertEquals(550.00, (float)$ledger->cost_ave);
        $this->assertEquals(550.00, (float)$ledger->totalamt);
        $this->assertEquals('Manually adjusted fee', $ledger->remarks);
    }
}
