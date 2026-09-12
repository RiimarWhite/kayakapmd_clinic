<?php

namespace App\Services\YakapManagement;

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
use App\Models\Profiles\ProfileBloodTypeModel;
use App\Models\Profiles\ProfileFamHistModel;
use App\Models\Profiles\ProfileFhSpecificModel;
use App\Models\Profiles\ProfileImmunizationModel;
use App\Models\Profiles\ProfileMedHistModel;
use App\Models\Profiles\ProfileMensHistModel;
use App\Models\Profiles\ProfileMhSpecificModel;
use App\Models\Profiles\ProfileNcdQansModel;
use App\Models\Profiles\ProfilePeGenSurveyModel;
use App\Models\Profiles\ProfilePeMiscModel;
use App\Models\Profiles\ProfilePepertModel;
use App\Models\Profiles\ProfilePeSpecificModel;
use App\Models\Profiles\ProfilePregHistModel;
use App\Models\Profiles\ProfileSocHistModel;
use App\Models\Profiles\ProfileSurgHistModel;
use App\Models\Soaps\SoapAdviceModel;
use App\Models\Soaps\SoapDiagnosticModel;
use App\Models\Soaps\SoapIcdModel;
use App\Models\Soaps\SoapManagementModel;
use App\Models\Soaps\SoapMedicineModel;
use App\Models\Soaps\SoapPeMiscModel;
use App\Models\Soaps\SoapPeSpecificModel;
use App\Models\Soaps\SoapPepertModel;
use App\Models\Soaps\SoapSubjectiveModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnlistmentService
{
    /**
     * Link a patient from PatientMasterlist to an Enlistment
     * Updates px_pin across all related records
     *
     * @param string $caseNo The enlistment case number (dCaseNo)
     * @param string $pxPin The patient PIN from PatientMasterlist (pincode)
     * @return array Success status and message
     */
    public function linkPatientToEnlistment(string $caseNo, string $pxPin): array
    {
        // Validate inputs
        if (empty($caseNo)) {
            throw new \InvalidArgumentException('Case number is required');
        }

        if (empty($pxPin)) {
            throw new \InvalidArgumentException('Patient PIN is required');
        }

        try {
            // Find the enlistment
            $enlistment = EnlistmentModel::where('dCaseNo', $caseNo)->first();

            if (!$enlistment) {
                throw new \InvalidArgumentException('Enlistment not found with case number: ' . $caseNo);
            }

            DB::beginTransaction();

            // Update EnlistmentModel
            $enlistment->px_pin = $pxPin;
            $enlistment->save();

            // Update ProfileModel (main profile table)
            ProfileModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);

            // Update SOAPModel (main soap table)
            SOAPModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);

            // Update DiagExamResult (main diagnostic exam results table)
            DiagExamResult::where('en_CaseNo', $caseNo)->update(['dPatientPin' => $pxPin]);

            // Update DiagExamResults subdirectory models (en_CaseNo - capital)
            DiagCbcModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagChestXrayModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagCreatineModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagEcgModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagFbsModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagFecalysisModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagFobtModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagHba1cModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagLipidProfileModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagOgttModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagOtherDiagExamModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagPapSmearModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagPpdTestModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagRbsModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagSputumModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            DiagUrinalysisModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);

            // Update Profiles subdirectory models (en_caseno - lowercase)
            ProfileBloodTypeModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfileFamHistModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfileFhSpecificModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfileImmunizationModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfileMedHistModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfileMensHistModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfileMhSpecificModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfileNcdQansModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfilePeGenSurveyModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfilePeMiscModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfilePepertModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfilePeSpecificModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfilePregHistModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfileSocHistModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            ProfileSurgHistModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);

            // Update Soaps subdirectory models (mixed case - en_caseno and en_CaseNo)
            SoapAdviceModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            SoapDiagnosticModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            SoapIcdModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            SoapManagementModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            SoapMedicineModel::where('en_CaseNo', $caseNo)->update(['px_pin' => $pxPin]);
            SoapPeMiscModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            SoapPeSpecificModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            SoapPepertModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);
            SoapSubjectiveModel::where('en_caseno', $caseNo)->update(['px_pin' => $pxPin]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Patient successfully linked to enlistment'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error linking patient to enlistment: ' . $e->getMessage(), [
                'caseNo' => $caseNo,
                'pxPin' => $pxPin,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
