<?php

namespace App\Services\YakapManagement;

use App\Models\KayakapProfileModel;
use App\Models\PCBModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Detailed Comment: YakapManagement PhilHealth ApiService
 * Manages communication with PhilHealth e-Claims REST API endpoints, handling authentication,
 * token acquisition and caching, registration status verification, ATC validation,
 * XML report validation, and encrypted XML transmittal submission.
 */
class ApiService
{
    private $token = null;

    /**
     * Detailed Comment: Retrieves facility credentials from PCBModel (pcb table).
     */
    private function getProfile()
    {
        return PCBModel::select(['userid', 'passwd', 'certificationid', 'hciaccreno'])->first();
    }

    /**
     * Detailed Comment: Retrieves base API URL for PhilHealth e-Claims web services.
     * Uses config('services.philhealth.url') with environment variable and sandbox fallbacks.
     */
    private function getBaseUrl(): string
    {
        return rtrim(config('services.philhealth.url', env('PHILHEALTH_URL', 'https://epress.philhealth.gov.ph')), '/');
    }

    /**
     * Detailed Comment: Requests an authentication token from PhilHealth web service.
     * Caches token in-memory on the service instance.
     */
    public function getToken()
    {
        $profile = $this->getProfile();

        if (!$profile) {
            throw new \Exception('PCB facility credentials profile not configured.');
        }

        $url = $this->getBaseUrl() . '/getToken';

        Log::info('PhilHealth API: Requesting authentication token', [
            'url' => $url,
            'hospital_code' => $profile->hciaccreno
        ]);

        $response = Http::asForm()
            ->timeout(30)
            ->post($url, [
                'pUserName' => $profile->userid ?? '',
                'pUserPassword' => $profile->passwd ?? '',
                'pSoftwareCertificationId' => $profile->certificationid ?? '',
                'pHospitalCode' => $profile->hciaccreno ?? ''
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->token = $data['Token'] ?? ($data['token'] ?? null);
            return $data;
        }

        Log::error('PhilHealth API: getToken error response', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        throw new \Exception($response->body() ?: 'Failed to acquire PhilHealth token.');
    }

    /**
     * Detailed Comment: Helper to retrieve an active token, fetching one if not yet retrieved.
     */
    public function getActiveToken(): ?string
    {
        if ($this->token) {
            return $this->token;
        }

        try {
            $data = $this->getToken();
            return is_array($data) ? ($data['Token'] ?? ($data['token'] ?? null)) : null;
        } catch (\Throwable $e) {
            Log::warning('PhilHealth API: Could not auto-acquire token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Detailed Comment: Checks whether a member or dependent is registered under PhilHealth.
     */
    public function isMemberDependentRegistered(array $params)
    {
        $token = $params['token'] ?? $this->getActiveToken();
        $url = $this->getBaseUrl() . '/isMemberDependentRegistered';

        $payload = [
            'Token' => $token,
            'pPin' => $params['pin'] ?? '',
            'pType' => $params['type'] ?? ''
        ];

        Log::info('PhilHealth API: isMemberDependentRegistered call', ['pin' => $params['pin'] ?? '']);

        $response = Http::asForm()
            ->timeout(30)
            ->post($url, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('PhilHealth API: isMemberDependentRegistered error', ['body' => $response->body()]);
        throw new \Exception($response->body() ?: 'Request failed with status ' . $response->status());
    }

    /**
     * Detailed Comment: Extracts registration list within a date range.
     */
    public function extractRegistrationList(array $params)
    {
        $token = $params['token'] ?? $this->getActiveToken();
        $url = $this->getBaseUrl() . '/extractRegistrationList';

        $payload = [
            'Token' => $token,
            'pStartDate' => $params['start'] ?? '',
            'pEndDate' => $params['end'] ?? ''
        ];

        Log::info('PhilHealth API: extractRegistrationList call', [
            'start' => $params['start'] ?? '',
            'end' => $params['end'] ?? ''
        ]);

        $response = Http::asForm()
            ->timeout(60)
            ->post($url, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('PhilHealth API: extractRegistrationList error', ['body' => $response->body()]);
        throw new \Exception($response->body() ?: 'Request failed with status ' . $response->status());
    }

    /**
     * Detailed Comment: Validates Authorization to Consult (ATC) for patient PIN.
     */
    public function isATCValid(array $params)
    {
        $token = $params['token'] ?? $this->getActiveToken();
        $url = $this->getBaseUrl() . '/isATCValid';

        $payload = [
            'Token' => $token,
            'pPIN' => $params['pin'] ?? '',
            'pATC' => $params['atc'] ?? '',
            'pEffectivityDate' => $params['effectivity_date'] ?? ''
        ];

        Log::info('PhilHealth API: isATCValid call', ['pin' => $params['pin'] ?? '', 'atc' => $params['atc'] ?? '']);

        $response = Http::asForm()
            ->timeout(30)
            ->post($url, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('PhilHealth API: isATCValid error', ['body' => $response->body()]);
        throw new \Exception($response->body() ?: 'Request failed with status ' . $response->status());
    }

    /**
     * Detailed Comment: Validates an XML report before submission.
     */
    public function validateReport(array $params)
    {
        $token = $params['token'] ?? $this->getActiveToken();
        $url = $this->getBaseUrl() . '/validateReport';

        $payload = [
            'Token' => $token,
            'pReport' => $params['report'] ?? '',
            'pReportTagging' => $params['tranche'] ?? '1'
        ];

        Log::info('PhilHealth API: validateReport call', ['tranche' => $params['tranche'] ?? '1']);

        $response = Http::asForm()
            ->timeout(60)
            ->post($url, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('PhilHealth API: validateReport error', ['body' => $response->body()]);
        throw new \Exception($response->body() ?: 'Validation request failed with status ' . $response->status());
    }

    /**
     * Detailed Comment: Submits an encrypted XML report transmittal payload to PhilHealth.
     */
    public function submitReport(array $params)
    {
        $token = $params['token'] ?? $this->getActiveToken();
        $url = $this->getBaseUrl() . '/submitReport';

        $payload = [
            'Token' => $token,
            'pTransmittalID' => $params['transmittal_id'] ?? '',
            'pReport' => $params['report'] ?? '',
            'pReportTagging' => $params['tranche'] ?? '1'
        ];

        Log::info('PhilHealth API: submitReport call', [
            'transmittal_id' => $params['transmittal_id'] ?? '',
            'tranche' => $params['tranche'] ?? '1'
        ]);

        $response = Http::asForm()
            ->timeout(60)
            ->post($url, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('PhilHealth API: submitReport error', ['body' => $response->body()]);
        throw new \Exception($response->body() ?: 'Submission request failed with status ' . $response->status());
    }
}
