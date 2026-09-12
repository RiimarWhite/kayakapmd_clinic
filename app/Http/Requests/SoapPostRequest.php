<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SoapPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $signsSymptoms = $this->input('subjectiveHistory.signsSymptoms', []);

        return [
            // Client Profile - Required
            'clientProfile.dSoapDate' => 'required',
            'clientProfile.dATC' => 'required',
            'clientProfile.dIsWalkedIn' => 'required',
            'clientProfile.dCoPay' => 'required',
            'clientProfile.consultCode' => 'nullable',

            // subject/history of illness
            'subjectiveHistory.signsSymptoms' => 'nullable|array',
            'subjectiveHistory.dOtherComplaint' => [
                Rule::requiredIf(in_array('X', $signsSymptoms)), // ✅ required if 'X' exists
                'nullable',
            ],
            'subjectiveHistory.dIllnessHistory' => 'nullable',
            'subjectiveHistory.dPainSite' => [
                Rule::requiredIf(in_array('38', $signsSymptoms)), // ✅ required if '38' exists
                'nullable',
            ],

            // objective/physical examination
            'objectivePhysicalExamination.dSystolicSoap' => 'required',
            'objectivePhysicalExamination.dDiastolicSoap' => 'required',
            'objectivePhysicalExamination.dHrSoap' => 'required',
            'objectivePhysicalExamination.dRrSoap' => 'required',
            'objectivePhysicalExamination.dTempSoap' => 'required',
            'objectivePhysicalExamination.dLeftVisionSoap' => 'nullable',
            'objectivePhysicalExamination.dRightVisionSoap' => 'nullable',
            'objectivePhysicalExamination.dHeightSoap' => 'required',
            'objectivePhysicalExamination.dWeightSoap' => 'required',
            'objectivePhysicalExamination.dBMISoap' => 'nullable',
            'objectivePhysicalExamination.dLengthSoap' => 'nullable',
            'objectivePhysicalExamination.dHeadCircSoap' => 'nullable',
            'objectivePhysicalExamination.dSkinfoldThicknessSoap' => 'nullable',
            'objectivePhysicalExamination.dWaistSoap' => 'nullable',
            'objectivePhysicalExamination.dHipSoap' => 'nullable',
            'objectivePhysicalExamination.dLimbsSoap' => 'nullable',
            'objectivePhysicalExamination.dMidUpperArmCircSoap' => 'nullable',

            'objectivePhysicalExamination.heent' => 'nullable|array',
            'objectivePhysicalExamination.dHeentRem' => 'nullable',
            'objectivePhysicalExamination.chest' => 'nullable|array',
            'objectivePhysicalExamination.dChestRem' => 'nullable',
            'objectivePhysicalExamination.heart' => 'nullable|array',
            'objectivePhysicalExamination.dHeartRem' => 'nullable',
            'objectivePhysicalExamination.abdomen' => 'nullable|array',
            'objectivePhysicalExamination.dAbdomenRem' => 'nullable',
            'objectivePhysicalExamination.dGuId' => 'nullable|array',
            'objectivePhysicalExamination.dGuRem' => 'nullable',
            'objectivePhysicalExamination.rectal' => 'nullable|array',
            'objectivePhysicalExamination.dRectalRem' => 'nullable',
            'objectivePhysicalExamination.skinExtremities' => 'nullable|array',
            'objectivePhysicalExamination.dSkinRem' => 'nullable',
            'objectivePhysicalExamination.neuro' => 'nullable|array',
            'objectivePhysicalExamination.dNeuroRem' => 'nullable',

            // assessment diagnosis (icd)
            'assessmentDiagnosis' => 'nullable|array',

            // plan management
            'planManagement.diagnostic_doctor_reco' => 'nullable|array',
            'planManagement.diagnostic_patient' => 'nullable|array',
            'planManagement.diagnostic_oth_remarks' => 'nullable',
            'planManagement.management' => 'nullable|array',
            'planManagement.management_oth_remarks1' => 'nullable',
            'planManagement.dRemarks' => 'nullable',

            // medicine
            'medsList' => 'nullable|array',

            'laboratoryResults' => 'nullable|array',

            'enlistmentCaseNo' => 'required',
            'soapTransNo' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'clientProfile.dSoapDate.required' => 'The Profile Date is required.',
            'clientProfile.dATC.required' => 'The ATC is required.',
            'clientProfile.dIsWalkedIn.required' => 'The Walk-in status is required.',
            'clientProfile.dCoPay.required' => 'The Co Pay is required.',

            'objectivePhysicalExamination.dSystolicSoap.required' => 'Systolic is required',
            'objectivePhysicalExamination.dDiastolicSoap.required' => 'Diastolic is required',
            'objectivePhysicalExamination.dHrSoap.required' => 'Heart rate is required',
            'objectivePhysicalExamination.dRrSoap.required' => 'Respiratory rate is required',
            'objectivePhysicalExamination.dTempSoap.required' => 'Temperature is required',
            'objectivePhysicalExamination.dHeightSoap.required' => 'Height is required',
            'objectivePhysicalExamination.dWeightSoap.required' => 'Weight is required',
        ];
    }
}
