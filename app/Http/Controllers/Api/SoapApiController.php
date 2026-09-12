<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SoapPostRequest;
use App\Models\ConsultationModel;
use App\Models\EnlistmentModel;
use App\Models\SOAPModel;
use App\Services\YakapManagement\ConsultationService;
use App\Services\YakapManagement\LaboratoryResultService;
use App\Services\YakapManagement\SoapService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Http\Request;

class SoapApiController extends Controller
{
    private $soapService;
    private $consultationService;
    private $laboratoryResultService;

    public function __construct(SoapService $soapService, ConsultationService $consultationService, LaboratoryResultService $laboratoryResultService)
    {
        $this->soapService = $soapService;
        $this->consultationService = $consultationService;
        $this->laboratoryResultService = $laboratoryResultService;
    }

    public function getSoapDetails($transNo): JsonResponse
    {
        $soapDetails = $this->soapService->getSoapDetails($transNo);

        if (! $soapDetails) {
            return response()->json(['message' => 'SOAP details not found'], 404);
        }

        $payload = $soapDetails->toArray();
        $payload['labResults'] = $this->soapService->getLaboratoryResultsForSoap($transNo);

        return response()->json($payload);
    }

    public function saveSoapData(SoapPostRequest $request)
    {
        $data = $request->validated();
        $data['laboratoryResults'] = $request->input('laboratoryResults', []);

        $this->soapService->saveSoapData($data);
        try {

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error in saveSoapData: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving consultation data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function linkConsultationCode(Request $request)
    {
        $data = $request->validate([
            'soapTransNo' => 'nullable',
            'enlistmentCaseNo' => 'required',
            'consultCode' => 'required'
        ]);

        try {
            if (isset($data['soapTransNo'])) {
                $soap = SOAPModel::where('pHciTransNo', $data['soapTransNo'])->first();
            } else {
                // create soap record
                $consultation = ConsultationModel::where('consultationrefno', $data['consultCode'])->first();
                $enlistment = EnlistmentModel::where('dCaseNo', $data['enlistmentCaseNo'])->first();

                if (!$consultation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'An error occurred while linking consultation data',
                    ], 500);
                }

                $soap = SOAPModel::create([
                    'pHciTransNo' => $this->soapService->generateSoapTransNo(),
                    'px_pin' => $enlistment->px_pin,
                    'px_consultcode_cn' => $data['consultCode'],
                    'en_CaseNo' => $data['enlistmentCaseNo'],
                    'dSoapDate' => $consultation->consultation_date,
                    'dPatientPin' => $enlistment->dPatientPin,
                    'dPatientType' => $enlistment->dPatientType,
                    'dMemPin' => $enlistment->dMemPin,
                    'dEffyear' => $enlistment->dEffyear,
                    'dATC' => '',
                    'dIsWalkedIn' => 'Y',
                    'dCoPay' => 0,
                    'dTransDate' => CarbonImmutable::now()->format('Y-m-d'),
                ]);
            }
            $enlistment = EnlistmentModel::where('dCaseNo', $data['enlistmentCaseNo'])->first();
            $consultCode = $data['consultCode'];

            $this->consultationService->linkConsultationCode($soap, $enlistment, $consultCode);

            return response()->json([
                'success' => true,
                'message' => 'Consultation successfully linked!',
                'soapTransNo' => $soap->pHciTransNo
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error linking consultcode: ' . $e->getMessage(), [
                'soap' => $data['soapTransNo'],
                'consultcode' => $data['consultCode'],
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while linking consultation data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function saveLabResult(Request $request)
    {
        $data = $request->validate([
            'soapTransNo'      => 'required|string',
            'enlistmentCaseNo' => 'required|string',
            'diagnosticId'     => 'required|string',
        ]);

        $enlistment = EnlistmentModel::where('dCaseNo', $data['enlistmentCaseNo'])->firstOrFail();
        $soap = SOAPModel::where('pHciTransNo', $data['soapTransNo'])->firstOrFail();

        $this->laboratoryResultService->saveSingle(
            $data['diagnosticId'],
            $data['enlistmentCaseNo'],
            $data['soapTransNo'],
            $soap->px_pin,
            $request->input('laboratoryResults', []),
            $enlistment,
        );

        return response()->json(['success' => true]);
    }

    public function checkFirstConsultation(string $selectedEnlistmentCaseNo)
    {
        try {
            $yearNow = CarbonImmutable::now()->year;
            $consultations = SOAPModel::where('en_CaseNo', $selectedEnlistmentCaseNo)->whereYear('dSoapDate', $yearNow)->get();

            if ($consultations->isEmpty()) {
                return response()->json([
                    'soapTransNo' => null,
                ], 200);
            } else {
                if ($consultations->count() == 1) {
                    return response()->json([
                        'soapTransNo' => $consultations[0]->pHciTransNo,
                    ], 200);
                } else {
                    return response()->json([
                        'soapTransNo' => null,
                    ], 200);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
