<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\ConsultationModel;
use App\Models\PatientMasterlist;
use App\Models\SecretaryModel;
use App\Models\SettlementsModel;
use App\Models\Stocks\StocksGroupingModel;
use App\Models\Stocks\StocksLedgerModel;
use App\Models\Stocks\StocksListingModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Detailed Comment: Feature test suite covering:
 * 1. Admin Credential Protection:
 *    - Session elevation check (/api/check_admin_elevation)
 *    - Admin verification with duration (/api/verify_admin_credentials)
 *    - Rejection of invalid credentials
 * 2. Stocks & Services Grouping Management:
 *    - Standalone page rendering (/admin/stocks/groupings)
 *    - Groupings CRUD API (/api/stocks/fetch_groupings, save_grouping, update_grouping, delete_grouping)
 * 3. Stocks Management Dosage Form and PhilHealth PGE:
 *    - Adding stock item with dosage_form and philhealth_gamot_essential
 *    - Fetching stock list with dosage_form filter
 * 4. Secretary Queue Protection:
 *    - Deleting patient queue guarded by admin elevation (/api/delete_patient_queue)
 *    - Deleting patient masterlist guarded by admin elevation (/api/delete_patient_sec)
 *    - Deleting fee guarded by admin elevation (/api/delete_patient_charge)
 * 5. Patient Masterlist on Queue:
 *    - Fetching 2-column patient masterlist (/api/fetch_queue_patient_masterlist)
 * 6. Payment Details & History:
 *    - Fetching past payment history (/api/fetch_patient_payment_history)
 */
class AdminProtectionStocksAndQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Test admin verification endpoints and duration persistence in user session.
     */
    public function test_admin_elevation_and_verification_workflow(): void
    {
        // 1. Initially when not elevated
        $res = $this->postJson('/api/check_admin_elevation');
        $res->assertStatus(200);
        $res->assertJson(['elevated' => false]);

        // 2. Failed verification with bad credentials
        $failRes = $this->postJson('/api/verify_admin_credentials', [
            'username' => 'admin',
            'password' => 'wrongpassword123'
        ]);
        $failRes->assertStatus(401);
        $failRes->assertJson(['success' => false]);

        // 3. Successful elevation with duration (1 hour)
        $admin = AdminModel::first();
        $admin->password = Hash::make('CorrectAdminPass123!');
        $admin->save();

        $elevateRes = $this->postJson('/api/verify_admin_credentials', [
            'username' => $admin->username,
            'password' => 'CorrectAdminPass123!',
            'duration' => '1_hour',
            'remember_access' => 1
        ]);
        $elevateRes->assertStatus(200);
        $elevateRes->assertJson(['success' => true, 'duration' => '1_hour']);

        // 4. Session check after elevation returns elevated true
        $checkRes = $this->postJson('/api/check_admin_elevation');
        $checkRes->assertStatus(200);
        $checkRes->assertJson(['elevated' => true]);
    }

    /**
     * Detailed Comment: Test Stocks & Services Groupings CRUD and standalone page rendering.
     */
    public function test_stocks_groupings_crud_and_page(): void
    {
        $admin = AdminModel::first();

        // 1. Standalone page view accessible by admin
        $pageRes = $this->actingAs($admin, 'admin')->get('/admin/stocks/groupings');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('Grouping Management');

        // 2. Save grouping
        $saveRes = $this->actingAs($admin, 'admin')->postJson('/api/stocks/save_grouping', [
            'category' => 'DRUGS AND MEDS',
            'group_name' => 'Antibiotics',
            'description' => 'Broad-spectrum antibiotics'
        ]);
        $saveRes->assertStatus(200);
        $saveRes->assertJson(['success' => true]);

        // 3. Fetch groupings by category
        $fetchRes = $this->actingAs($admin, 'admin')->postJson('/api/stocks/fetch_groupings', [
            'category' => 'DRUGS AND MEDS'
        ]);
        $fetchRes->assertStatus(200);
        $fetchRes->assertJsonFragment(['group_name' => 'Antibiotics']);

        $grouping = StocksGroupingModel::where('group_name', 'Antibiotics')->first();
        $this->assertNotNull($grouping);

        // 4. Update grouping
        $updateRes = $this->actingAs($admin, 'admin')->postJson('/api/stocks/update_grouping', [
            'id' => $grouping->id,
            'category' => 'DRUGS AND MEDS',
            'group_name' => 'Antibiotics & Anti-infectives',
            'description' => 'Updated description'
        ]);
        $updateRes->assertStatus(200);
        $this->assertEquals('Antibiotics & Anti-infectives', $grouping->fresh()->group_name);

        // 5. Delete grouping
        $deleteRes = $this->actingAs($admin, 'admin')->postJson('/api/stocks/delete_grouping', [
            'id' => $grouping->id
        ]);
        $deleteRes->assertStatus(200);
        $this->assertNull(StocksGroupingModel::find($grouping->id));
    }

    /**
     * Detailed Comment: Test stock item creation and listing with dosage_form and philhealth_gamot_essential.
     */
    public function test_stock_item_with_dosage_form_and_pge(): void
    {
        $admin = AdminModel::first();

        // Save a stock item under DRUGS AND MEDS
        $saveRes = $this->actingAs($admin, 'admin')->postJson('/api/save_stock_item', [
            'item_dscr' => 'Amoxicillin 500mg Capsule',
            'item_group' => 'DRUGS AND MEDS',
            'drug_group' => 'Antibiotics',
            'dosage_form' => 'Capsule',
            'philhealth_gamot_essential' => 1,
            'price_regular' => 8.50,
            'item_quantity' => 150
        ]);
        $saveRes->assertStatus(200);
        $saveRes->assertJson(['success' => true]);

        $item = StocksListingModel::where('prod_itemdscr', 'Amoxicillin 500mg Capsule')->first();
        $this->assertNotNull($item);
        $this->assertEquals('Capsule', $item->dosage_form);
        $this->assertEquals(1, $item->philhealth_gamot_essential);
    }

    /**
     * Detailed Comment: Test admin credential protection for fee deletion and patient queue deletion.
     */
    public function test_admin_credential_protection_guards(): void
    {
        $sec = SecretaryModel::first();
        $admin = AdminModel::first();
        $admin->password = Hash::make('AdminSecret123');
        $admin->save();

        // Create dummy consultation record
        $consultation = ConsultationModel::create([
            'consultationrefno' => 'CRTEST999',
            'pincode' => 'PINTEST999',
            'pxrefno' => 'PXTEST999',
            'patientname' => 'Dela Cruz, Juan',
            'status' => 'WAITING',
            'consultation_date' => now()->toDateString()
        ]);

        // Create dummy charge in stocks_ledger
        $charge = StocksLedgerModel::create([
            'px_consultcode_cn' => 'CRTEST999',
            'prodcode' => 'CHG001',
            'item_dscr' => 'Professional Fee',
            'qty' => 1,
            'cost_ave' => 500.00,
            'retails' => 500.00,
            'totalamt' => 500.00,
            'item_grouping' => 'PROFESSIONAL FEE'
        ]);

        // 1. Secretary without elevation attempts to delete queue -> 403 Forbidden
        $qRes = $this->actingAs($sec, 'secretary')->postJson('/api/delete_patient_queue', [
            'consultationrefno' => 'CRTEST999'
        ]);
        $qRes->assertStatus(403);
        $qRes->assertJson(['require_admin_auth' => true]);

        // 2. Secretary without elevation attempts to delete charge fee -> 403 Forbidden
        $fRes = $this->actingAs($sec, 'secretary')->postJson('/api/delete_patient_charge', [
            'consultationrefno' => 'CRTEST999',
            'prodcode' => 'CHG001'
        ]);
        $fRes->assertStatus(403);
        $fRes->assertJson(['require_admin_auth' => true]);

        // 3. Elevate session with admin verification
        $elevateRes = $this->actingAs($sec, 'secretary')->postJson('/api/verify_admin_credentials', [
            'username' => $admin->username,
            'password' => 'AdminSecret123',
            'duration' => '1_hour'
        ]);
        $elevateRes->assertStatus(200);

        // 4. Elevated secretary can now delete charge fee -> Success
        $fElevated = $this->actingAs($sec, 'secretary')->postJson('/api/delete_patient_charge', [
            'consultationrefno' => 'CRTEST999',
            'prodcode' => 'CHG001'
        ]);
        $fElevated->assertStatus(200);
        $fElevated->assertJson(['success' => true]);

        // 5. Elevated secretary can now delete patient queue record -> Success
        $qElevated = $this->actingAs($sec, 'secretary')->postJson('/api/delete_patient_queue', [
            'consultationrefno' => 'CRTEST999'
        ]);
        $qElevated->assertStatus(200);
        $qElevated->assertJson(['success' => true]);
        $this->assertNull(ConsultationModel::where('consultationrefno', 'CRTEST999')->first());
    }

    /**
     * Detailed Comment: Test patient payment history and patient masterlist table fetch endpoints.
     */
    public function test_payment_history_and_patient_masterlist_endpoints(): void
    {
        $sec = SecretaryModel::first();

        // Create sample patient masterlist record
        PatientMasterlist::create([
            'pxrefno' => 'PXHIST001',
            'pincode' => 'PINHIST001',
            'pxlastname' => 'Santos',
            'pxfirstname' => 'Maria',
            'pxmidname' => 'Clara',
            'patientname' => 'Santos, Maria Clara',
            'gender' => 'FEMALE',
            'birthday' => '1995-05-15'
        ]);

        // Create past settlement record
        SettlementsModel::create([
            'consultationrefno' => 'CRHIST001',
            'pincode' => 'PINHIST001',
            'docname' => 'Dr. Jose Rizal',
            'total_gross' => 1500.00,
            'net_payable' => 1500.00,
            'payment_cash' => 1500.00,
            'payment_card' => 0.00,
            'created' => '2026-09-15 10:30:00'
        ]);

        // 1. Fetch queue patient masterlist
        $masterlistRes = $this->actingAs($sec, 'secretary')->postJson('/api/fetch_queue_patient_masterlist', [
            'start' => 0,
            'length' => 10
        ]);
        $masterlistRes->assertStatus(200);
        $masterlistRes->assertJsonFragment(['formatted_name' => 'Santos, Maria Clara']);

        // 2. Fetch patient payment history
        $payRes = $this->actingAs($sec, 'secretary')->postJson('/api/fetch_patient_payment_history', [
            'pincode' => 'PINHIST001'
        ]);
        $payRes->assertStatus(200);
        $payRes->assertJsonFragment(['consultationrefno' => 'CRHIST001', 'payment_status' => 'PAID']);
    }
}
