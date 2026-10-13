<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\EnlistmentModel;
use App\Models\KayakapProfileModel;
use App\Models\PCBModel;
use App\Models\SOAPModel;
use App\Models\XmlTransModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Detailed Comment: Feature test suite for PhilHealth e-Claims XML Generation, Encryption,
 * Retrieval, and Web Service Transmission endpoints.
 */
class PhilHealthXmlAndTransmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\AdminSeeder::class);

        // Ensure facility credentials exist in pcb table
        PCBModel::updateOrCreate(
            ['clientcode' => '122377'],
            [
                'userid' => 'test_user',
                'passwd' => 'test_pass',
                'certificationid' => 'CERT-123456',
                'hciaccreno' => 'H99999999',
                'pmccno' => 'PMCC-001'
            ]
        );

        KayakapProfileModel::updateOrCreate(
            ['clientcode' => '122377'],
            [
                'HOSP_NAME' => 'KayakapMD Test Clinic',
                'EMAIL_ADD' => 'test@kayakapmd.com',
                'DATE_REGISTERED' => '2026-01-01'
            ]
        );
    }

    /**
     * Detailed Comment: Authenticates an admin session for testing protected routes.
     */
    protected function authenticateAdmin()
    {
        $admin = AdminModel::first() ?? AdminModel::create([
            'dw_clientcode' => '122377',
            'username' => 'testadmin',
            'password' => bcrypt('password123'),
            'adminname' => 'Test Admin',
            'status' => 'ACTIVE'
        ]);

        return $this->actingAs($admin, 'admin');
    }

    /**
     * Detailed Comment: Tests the get_xml_data endpoint returning facility accreditation number.
     */
    public function test_get_xml_data_returns_accreno()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/get_xml_data');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => 'H99999999'
            ]);
    }

    /**
     * Detailed Comment: Tests generating Tranche 1 XML report and persisting to xml_transmittal_reports.
     */
    public function test_generate_xml_first_tranche_and_fetch()
    {
        $this->authenticateAdmin();

        $pin = 'TESTPIN' . rand(1000, 9999);
        $caseNo = 'CASE' . rand(1000, 9999);

        // Create test enlistment
        EnlistmentModel::create([
            'dw_clientcode' => '122377',
            'dCaseNo' => $caseNo,
            'dTransNo' => 'TRANS-001',
            'px_pin' => $pin,
            'dPatientPin' => $pin,
            'dPatientFname' => 'Juan',
            'dPatientLname' => 'Dela Cruz',
            'dPatientSex' => 'M',
            'dPatientDob' => '1990-01-01',
            'dMemDob' => '1990-01-01',
            'dTransDate' => '2026-01-01',
            'dEffyear' => 2026,
            'dEnlistStat' => '1',
            'dEnlistDate' => '2026-01-01',
            'dPackageType' => 'K'
        ]);

        // Create consulted SOAP record
        SOAPModel::create([
            'dw_clientcode' => '122377',
            'en_CaseNo' => $caseNo,
            's_TransNo' => 'TRANS-001',
            'px_pin' => $pin,
            'dSoapDate' => now(),
            'dTransDate' => now(),
            'status' => 'CONSULTED'
        ]);

        $payload = [
            'data' => [
                [
                    'px_pin' => $pin,
                    'en_CaseNo' => $caseNo
                ]
            ],
            'type' => 'first',
            'start' => now()->subDay()->format('Y-m-d'),
            'end' => now()->addDay()->format('Y-m-d')
        ];

        $response = $this->postJson('/api/generate_xml_first_tranche', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertNotEmpty($response->json('document'));
        $this->assertStringContainsString('<PCB', $response->json('document'));
        $this->assertStringContainsString('pHciAccreno="H99999999"', $response->json('document'));

        // Verify record in xml_transmittal_reports
        $xmlRecord = XmlTransModel::where('dw_clientcode', '122377')->latest('id')->first();
        $this->assertNotNull($xmlRecord);
        $reportCode = $xmlRecord->report_code;

        // Test fetch_xml_data
        $fetchResponse = $this->postJson('/api/fetch_xml_data', [
            'reference' => $reportCode
        ]);

        $fetchResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'report_code' => $reportCode
            ]);

        // Test encrypt_xml
        $encryptResponse = $this->postJson('/api/encrypt_xml', [
            'reference' => $reportCode
        ]);

        $encryptResponse->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        // Verify encrypted content saved in database
        $xmlRecord->refresh();
        $this->assertNotEmpty($xmlRecord->ENCRYPTED_CONTENT);

        // Test decrypt_xml
        $decryptResponse = $this->postJson('/api/decrypt_xml', [
            'reference' => $reportCode
        ]);

        $decryptResponse->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
        $this->assertStringContainsString('<PCB', $decryptResponse->json('document'));
    }

    /**
     * Detailed Comment: Tests PhilHealth API client endpoints using Http::fake().
     */
    public function test_philhealth_api_controller_endpoints()
    {
        $this->authenticateAdmin();

        Http::fake([
            '*/getToken' => Http::response([
                'Token' => 'mocked-jwt-token-xyz',
                'Status' => 'SUCCESS'
            ], 200),
            '*/isMemberDependentRegistered' => Http::response([
                'success' => true,
                'registered' => true
            ], 200),
            '*/isATCValid' => Http::response([
                'success' => true,
                'valid' => true
            ], 200),
            '*/submitReport' => Http::response([
                'success' => true,
                'transmittalNumber' => 'TR-99999'
            ], 200)
        ]);

        // 1. generateToken
        $tokenRes = $this->postJson('/api/generate_token');
        $tokenRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'Token' => 'mocked-jwt-token-xyz'
                ]
            ]);

        // 2. checkRegistration
        $regRes = $this->postJson('/api/check_registration', [
            'pin' => '123456789012',
            'type' => 'MM'
        ]);
        $regRes->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        // 3. validate_atc
        $atcRes = $this->postJson('/api/validate_atc', [
            'pin' => '123456789012',
            'atc' => 'ATC-XYZ',
            'effectivity_date' => '2026-10-13'
        ]);
        $atcRes->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        // 4. submit_xml
        $submitRes = $this->postJson('/api/submit_xml', [
            'report' => '<test_xml></test_xml>',
            'transmittal_id' => 'REP-001',
            'tranche' => '1'
        ]);
        $submitRes->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /**
     * Detailed Comment: Tests second tranche XML generation with prerequisite validation.
     */
    public function test_generate_xml_second_tranche_requires_tranche_one()
    {
        $this->authenticateAdmin();

        $pin = 'TESTPIN' . rand(1000, 9999);
        $caseNo = 'CASE' . rand(1000, 9999);

        EnlistmentModel::create([
            'dw_clientcode' => '122377',
            'dCaseNo' => $caseNo,
            'dTransNo' => 'TRANS-002',
            'px_pin' => $pin,
            'dPatientPin' => $pin,
            'dPatientFname' => 'Maria',
            'dPatientLname' => 'Santos',
            'dPatientSex' => 'F',
            'dPatientDob' => '1995-05-05',
            'dMemDob' => '1995-05-05',
            'dTransDate' => '2026-01-01',
            'dEffyear' => 2026,
            'dEnlistStat' => '1',
            'dEnlistDate' => '2026-01-01',
            'dPackageType' => 'K'
        ]);

        SOAPModel::create([
            'dw_clientcode' => '122377',
            'en_CaseNo' => $caseNo,
            's_TransNo' => 'TRANS-002',
            'px_pin' => $pin,
            'dSoapDate' => now(),
            'dTransDate' => now(),
            'status' => 'CONSULTED',
            'report_code' => null // No tranche 1 yet
        ]);

        $payload = [
            'data' => [
                [
                    'px_pin' => $pin,
                    'en_CaseNo' => $caseNo
                ]
            ],
            'type' => 'second',
            'start' => now()->subDay()->format('Y-m-d'),
            'end' => now()->addDay()->format('Y-m-d')
        ];

        $response = $this->postJson('/api/generate_xml_first_tranche', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'One or more records does not have a tranche 1 report yet.'
            ]);
    }
}
