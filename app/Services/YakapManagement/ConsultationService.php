<?php

namespace App\Services\YakapManagement;

use App\Models\ConsultationModel;
use App\Models\EnlistmentModel;
use App\Models\ProfileModel;
use App\Models\SOAPModel;
use App\Models\DiagExamResult;
use App\Models\DiagExamResults\DiagCbcModel;
use App\Models\DiagExamResults\DiagChestXrayModel;
use App\Models\DiagExamResults\DiagCreatineModel;
use App\Models\DiagExamResults\DiagEcgModel;
use App\Models\DiagExamResults\DiagFbsModel;
use App\Models\DiagExamResults\DiagFecalysisModel;
use App\Models\DiagExamResults\DiagFobtModel;
use App\Models\DiagExamResults\DiagHba1cModel;
use App\Models\DiagExamResults\DiagLipidProfileModel;
use App\Models\DiagExamResults\DiagOgttModel;
use App\Models\DiagExamResults\DiagOtherDiagExamModel;
use App\Models\DiagExamResults\DiagPapSmearModel;
use App\Models\DiagExamResults\DiagPpdTestModel;
use App\Models\DiagExamResults\DiagRbsModel;
use App\Models\DiagExamResults\DiagSputumModel;
use App\Models\DiagExamResults\DiagUrinalysisModel;
use App\Models\PhicChargesModel;
use App\Models\Soaps\SoapAdviceModel;
use App\Models\Soaps\SoapDiagnosticModel;
use App\Models\Soaps\SoapIcdModel;
use App\Models\Soaps\SoapManagementModel;
use App\Models\Soaps\SoapMedicineModel;
use App\Models\Soaps\SoapPeMiscModel;
use App\Models\Soaps\SoapPeSpecificModel;
use App\Models\Soaps\SoapPepertModel;
use App\Models\Soaps\SoapSubjectiveModel;
use App\Models\StocksLedger;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsultationService
{
    public function linkConsultationCode($soap, $enlistment, string $consultCode): array
    {
        try {
            DB::beginTransaction();

            SOAPModel::where('pHciTransNo', $soap->pHciTransNo)->update(['px_consultcode_cn' => $consultCode]);

            $soapTransNo = $soap->pHciTransNo;

            // Update DiagExamResult (main diagnostic exam results table)
            // DiagExamResult::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]); TODO: verify

            // Update DiagExamResults subdirectory models (en_soapTransNo - capital)
            DiagCbcModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagChestXrayModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagCreatineModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagEcgModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagFbsModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagFecalysisModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagFobtModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagHba1cModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagLipidProfileModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagOgttModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagOtherDiagExamModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagPapSmearModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagPpdTestModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagRbsModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagSputumModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            DiagUrinalysisModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);

            // Update Soaps subdirectory models (mixed case - s_TransNo and s_TransNo)
            SoapAdviceModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            SoapDiagnosticModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            SoapIcdModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            SoapManagementModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            SoapMedicineModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            SoapPeMiscModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            SoapPeSpecificModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            SoapPepertModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);
            SoapSubjectiveModel::where('s_TransNo', $soapTransNo)->update(['px_consultcode_cn' => $consultCode]);

            $profile = ProfileModel::where('en_CaseNo', $enlistment->dCaseNo)->first();
            $p_transno = $profile->dTransNo ?? null;

            // update pxwalkinconsultation
            ConsultationModel::where('consultationrefno', $consultCode)->update([
                'soap_transno' => $soapTransNo,
                'en_caseno' => $enlistment->dCaseNo,
                'p_transno' => $p_transno
            ]);

            // update phic_charges
            PhicChargesModel::where('px_consultcode_cn', $consultCode)->update([
                'en_caseno' => $enlistment->dCaseNo,
                's_TransNo' => $soapTransNo,
                'updated' => CarbonImmutable::now()
            ]);

            // update stocks ledger
            StocksLedger::where('px_consultcode_cn', $consultCode)->update([
                'en_CaseNo' => $enlistment->dCaseNo,
                's_transno' => $soapTransNo,
                'updated' => CarbonImmutable::now()
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Consultation successfully linked!'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error linking consultcode: ' . $e->getMessage(), [
                'soap' => $soap,
                'consultcode' => $consultCode,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
