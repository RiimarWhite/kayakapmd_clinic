<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HMOModel;
use App\Services\YakapManagement\ApiService;

use Illuminate\Http\Request;

class PhilHealthApiController extends Controller
{
    private $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function generateToken()
    {
        $this->apiService->getToken();
    }

    public function checkRegistration(Request $request)
    {
        $this->apiService->isMemberDependentRegistered([
            'pin' => $request->pin,
            'type' => $request->type
        ]);
    }

    public function fetchRegistrations(Request $request)
    {
        $this->apiService->extractRegistrationList([
            'start' => $request->start,
            'end' => $request->end
        ]);
    }

    public function checkATC(Request $request)
    {
        $this->apiService->isATCValid([
            'pin' => $request->pin,
            'atc' => $request->atc,
            'effectivity_date' => $request->effectivity_date
        ]);
    }

    public function validateXML(Request $request)
    {
        $this->apiService->validateReport([
            'report' => $request->report,
            'tranche' => $request->tranche
        ]);
    }

    public function submitXML(Request $request)
    {
        $this->apiService->submitReport([
            'transmittal_id' => $request->transmittal_id,
            'report' => $request->report,
            'tranche' => $request->tranche
        ]);
    }
}
