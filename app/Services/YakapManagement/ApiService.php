<?php

namespace App\Services\YakapManagement;

use App\Models\KayakapProfileModel;
use App\Models\PCBModel;

use Http;

class ApiService
{
    private $token;

    private function getProfile()
    {
        return PCBModel::select(['userid',  'passwd', 'certificationid', 'hciaccreno'])->first();
    }

    public function getToken()
    {
        $profile = $this->getProfile();

        $response = Http::asForm()
            ->post(env('PHILHEALTH_URL') . '/getToken', [
                'pUserName' => $profile->userid,
                'pUserPassword' => $profile->passwd,
                'pSoftwareCertificationId' => $profile->certificationid,
                'pHospitalCode' => $profile->hciaccreno
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception($response->body());
    }

    public function isMemberDependentRegistered(array $params)
    {
        $profile = $this->getProfile();

        $response = Http::asForm()
            ->post(env('PHILHEALTH_URL') . '/isMemberDependentRegistered', [
                'Token',
                'pPin' => $params['pin'],
                'pType' => $params['type']
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception($response->body());
    }

    public function extractRegistrationList(array $params)
    {
        $profile = $this->getProfile();

        $response = Http::asForm()
            ->post(env('PHILHEALTH_URL') . '/extractRegistrationList', [
                'Token',
                'pStartDate' => $params['start'],
                'pEndDate' => $params['end']
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception($response->body());
    }

    public function isATCValid(array $params)
    {
        $profile = $this->getProfile();

        $response = Http::asForm()
            ->post(env('PHILHEALTH_URL') . '/isATCValid', [
                'Token',
                'pPIN' => $params['pin'],
                'pATC' => $params['atc'],
                'pEffectivityDate' => $params['effectivity_date']
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception($response->body());
    }

    public function validateReport(array $params)
    {
        $profile = $this->getProfile();

        $response = Http::asForm()
            ->post(env('PHILHEALTH_URL') . '/validateReport', [
                'Token',
                'pReport' => $params['report'],
                'pReportTagging' => $params['tranche']
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception($response->body());
    }

    public function submitReport(array $params)
    {
        $profile = $this->getProfile();

        $response = Http::asForm()
            ->post(env('PHILHEALTH_URL') . '/submitReport', [
                'Token',
                'pTransmittalID' => $params['transmittal_id'],
                'pReport' => $params['report'],
                'pReportTagging' => $params['tranche']

            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception($response->body());
    }


}
