<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use App\Services\YakapManagement\SoapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $soapService;
    protected $clientService;

    public function __construct(SoapService $soapService, ClientService $clientService)
    {
        $this->soapService = $soapService;
        $this->clientService = $clientService;
    }

    public function generateEkas($transNo)
    {
        // Fetch SOAP data
        $soap = $this->soapService->getSoapDetailsForEkasReport($transNo);

        if (!$soap) {
            return response()->json(['error' => 'SOAP data not found for transaction number: ' . $transNo], 404);
        }

        $client = $this->clientService->getPcbDetails();

        $data = [
            'hciName' => $client->facilityName ?? '',
            'accreno' => $client->hciaccreno ?? '',
            'caseNo' => $soap->en_CaseNo ?? '',
            'transNo' => $soap->pHciTransNo ?? '',
            'patientName' => $soap->enlistment->patientname ?? '',
            'age' => CarbonImmutable::parse($soap->enlistment->dPatientDob)->age ?? '',
            'pxPin' => $soap->enlistment->dPatientPin ?? '',
            'pxContactNo' => $soap->enlistment->dPatientMobileNo ?? '',
            'pxATC' => $soap->dATC ?? '',
            'pxSex' => $soap->enlistment->dPatientSex ?? '',
            'membershipCategory' => 'Regular (Dummy)',
            'membershipType' => $soap->enlistment->dPatientType ?? '',
            'diagnostics' => $soap->diagnostics ?? [],
        ];

        $pdf = Pdf::loadView('reports.ekas', $data);
        return $pdf->stream('eKAS_' . $transNo . '.pdf');
    }

    public function generateEpress($transNo)
    {
        // Fetch SOAP data
        $soap = $this->soapService->getSoapDetailsEpressForReport($transNo);

        if (!$soap) {
            return response()->json(['error' => 'SOAP data not found for transaction number: ' . $transNo], 404);
        }

        $medicines = collect($soap->phicCharges)
            ->where('item_grouping', 'DRUGS AND MEDS')
            ->values();

        $client = $this->clientService->getPcbDetails();

        $data = [
            'hciName' => $client->facilityName ?? '',
            'accreno' => $client->hciaccreno ?? '',
            'caseNo' => $soap->en_CaseNo ?? '',
            'transNo' => $soap->pHciTransNo ?? '',
            'patientName' => $soap->enlistment->patientname ?? '',
            'age' => CarbonImmutable::parse($soap->enlistment->dPatientDob)->age ?? '',
            'pxPin' => $soap->enlistment->dPatientPin ?? '',
            'pxContactNo' => $soap->enlistment->dPatientMobileNo ?? '',
            'membershipCategory' => 'Regular (Dummy)',
            'membershipType' => $soap->enlistment->dPatientType ?? '',
            'pxATC' => $soap->dATC ?? '',
            'medicines' => $medicines,
        ];

        $pdf = Pdf::loadView('reports.epress', $data);
        return $pdf->stream('ePresS_' . $transNo . '.pdf');
    }
}
