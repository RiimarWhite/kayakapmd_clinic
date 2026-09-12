<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfilePostRequest extends FormRequest
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
        return [
            // Client Profile - Required
            'clientProfile.dProfDate' => 'required',
            'clientProfile.dATC' => 'required',
            'clientProfile.dIsWalkedIn' => 'required',

            // Family History
            'familyHistory.chkFamHistDiseases' => 'required|array',
            'familyHistory.chkFamHistDiseases.*.selected' => 'sometimes|required',
            'familyHistory.chkFamHistDiseases.*.specify' => 'nullable',

            // Medical History
            'medicalHistory.chkMedHistDiseases' => 'required|array',
            'medicalHistory.chkMedHistDiseases.*.selected' => 'sometimes|required',
            'medicalHistory.chkMedHistDiseases.*.specify' => 'nullable',

            // Menstrual History - Required if applicable
            'mensHistory' => 'array',

            // Pregnancy History - Required if applicable
            'pregHistory' => 'array',

            // Personal Social History - Required
            'personalSocialHistory.dIsSmoker' => 'required',
            'personalSocialHistory.dNoCigpk' => 'required_if:personalSocialHistory.dIsSmoker,Y',
            'personalSocialHistory.dIsADrinker' => 'required',
            'personalSocialHistory.dNoBottles' => 'required_if:personalSocialHistory.dIsADrinker,Y',
            'personalSocialHistory.dIllDrugUser' => 'required',
            'personalSocialHistory.dIsSexuallyActive' => 'required',

            // Immunizations
            'immunizations.chkImmChild' => 'nullable|array',
            'immunizations.chkImmAdult' => 'nullable|array',
            'immunizations.chkImmPregnant' => 'nullable|array',
            'immunizations.chkImmElderly' => 'nullable|array',
            'immunizations.dOtherImm' => 'nullable',

            // Physical Exam Pertinent - Required vitals
            'pepert.dSystolic' => 'required',
            'pepert.dDiastolic' => 'required',
            'pepert.dHr' => 'required',
            'pepert.dRr' => 'required',
            'pepert.dTemp' => 'required',
            'pepert.dHeight' => 'required',
            'pepert.dWeight' => 'required',
            'pepert.dBMI' => 'nullable',
            'pepert.dLeftVision' => 'nullable',
            'pepert.dRightVision' => 'nullable',
            'pepert.dLength' => 'nullable',
            'pepert.dHeadCirc' => 'nullable',
            'pepert.dSkinfoldThickness' => 'nullable',
            'pepert.dMidUpperArmCirc' => 'nullable',
            'pepert.dWaist' => 'nullable',
            'pepert.dHip' => 'nullable',
            'pepert.dLimbs' => 'nullable',

            // Blood Type
            'bloodType.dBloodType' => 'nullable',

            // General Survey
            'generalSurvey.dGenSurveyId' => 'nullable',
            'generalSurvey.dGenSurveyRem' => 'nullable',

            // Pertinent Findings - All optional
            'pertinentFindings.heent' => 'nullable|array',
            'pertinentFindings.dHeentRem' => 'nullable',
            'pertinentFindings.chest' => 'nullable|array',
            'pertinentFindings.dChestRem' => 'nullable',
            'pertinentFindings.heart' => 'nullable|array',
            'pertinentFindings.dHeartRem' => 'nullable',
            'pertinentFindings.abdomen' => 'nullable|array',
            'pertinentFindings.dAbdomenRem' => 'nullable',
            'pertinentFindings.dGuId' => 'nullable|array',
            'pertinentFindings.dGuRem' => 'nullable',
            'pertinentFindings.rectal' => 'nullable|array',
            'pertinentFindings.dRectalRem' => 'nullable',
            'pertinentFindings.skinExtremities' => 'nullable|array',
            'pertinentFindings.dSkinRem' => 'nullable',
            'pertinentFindings.neuro' => 'nullable|array',
            'pertinentFindings.dNeuroRem' => 'nullable',

            // NCD High Risk - All optional for now
            'ncdHighRisk' => 'nullable|array',

            // Enlistment and Profile Transaction Numbers
            'enlistmentCaseNo' => 'required',
            'profileTransNo' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            // Client Profile
            'clientProfile.dProfDate.required' => 'The Profile Date is required.',
            'clientProfile.dATC.required' => 'The ATC is required.',
            'clientProfile.dIsWalkedIn.required' => 'The Walk-in status is required.',

            // Family History
            'familyHistory.chkFamHistDiseases.required' => 'Family history diseases are required.',
            'familyHistory.chkFamHistDiseases.array' => 'Family history diseases must be an array.',
            'familyHistory.chkFamHistDiseases.*.selected.required' => 'A family history disease selection is required.',

            // Medical History
            'medicalHistory.chkMedHistDiseases.required' => 'Medical history diseases are required.',
            'medicalHistory.chkMedHistDiseases.array' => 'Medical history diseases must be an array.',
            'medicalHistory.chkMedHistDiseases.*.selected.required' => 'A medical history disease selection is required.',

            // Personal Social History
            'personalSocialHistory.dIsSmoker.required' => 'Smoking status is required.',
            'personalSocialHistory.dNoCigpk.required_if' => 'Number of cigarette packs is required when the patient is a smoker.',
            'personalSocialHistory.dIsADrinker.required' => 'Drinking status is required.',
            'personalSocialHistory.dNoBottles.required_if' => 'Number of bottles is required when the patient is a drinker.',
            'personalSocialHistory.dIllDrugUser.required' => 'Illegal drug use status is required.',
            'personalSocialHistory.dIsSexuallyActive.required' => 'Sexual activity status is required.',

            // Physical Exam Pertinent
            'pepert.dSystolic.required' => 'Systolic blood pressure is required.',
            'pepert.dDiastolic.required' => 'Diastolic blood pressure is required.',
            'pepert.dHr.required' => 'Heart rate is required.',
            'pepert.dRr.required' => 'Respiratory rate is required.',
            'pepert.dTemp.required' => 'Temperature is required.',
            'pepert.dHeight.required' => 'Height is required.',
            'pepert.dWeight.required' => 'Weight is required.',

            // Enlistment
            'enlistmentCaseNo.required' => 'The Enlistment Case Number is required.',
        ];
    }
}
