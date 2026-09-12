<?php

namespace App\Services\YakapManagement;

use App\Models\EnlistmentModel;
use App\Models\ProfileModel;
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
use App\Services\ClientService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    private $caseNo;

    private $profileTransNo;
    private $clientCode;
    private $pxPin;

    public function __construct(
        private ClientService $clientService
    )
    {
        $this->clientCode = $this->clientService->getClientCode();
    }

    public function saveProfileData(array $data)
    {
        if (! isset($data['enlistmentCaseNo']) || $data['enlistmentCaseNo'] === null) {
            throw new \InvalidArgumentException('Missing required identifiers: enlistmentCaseNo and profileTransNo');
        }

        $this->caseNo = $data['enlistmentCaseNo'];

        try {
            $enlistment = EnlistmentModel::where('dCaseNo', $this->caseNo)->first();

            if (! $enlistment) {
                throw new \InvalidArgumentException('Enlistment with the provided case number does not exist');
            }

            $this->pxPin = $enlistment->px_pin;

            DB::beginTransaction();

            if (! isset($data['profileTransNo']) || $data['profileTransNo'] === null) {
                $this->profileTransNo = $this->generateProfileTransNo();
                $this->createProfileRecord($data['clientProfile'] ?? []);
            } else {
                $this->profileTransNo = $data['profileTransNo'];
            }

            $this->saveProfileDetails($data['clientProfile'] ?? []);
            $this->saveMedicalHistory($data['medicalHistory'] ?? []);
            $this->saveSurgicalHistory($data['surgicalHistory'] ?? []);
            $this->saveFamilyHistory($data['familyHistory'] ?? []);
            $this->savePersonalSocialHistory($data['personalSocialHistory'] ?? []);
            $this->saveImmunization($data['immunizations'] ?? []);
            $this->saveMensHistory($data['mensHistory'] ?? []);
            $this->savePregHistory($data['pregHistory'] ?? []);
            $this->savePepert($data['pepert'] ?? []);
            $this->saveBloodType($data['bloodType'] ?? []);
            $this->saveGeneralSurvey($data['generalSurvey'] ?? []);
            $this->savePertinentFindings($data['pertinentFindings'] ?? []);
            $this->saveNCDHighRiskAssessment($data['ncdHighRisk'] ?? []);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; // Rethrow the exception after rolling back
        }
    }

    private function createProfileRecord($clientProfileData)
    {
        $enlistment = EnlistmentModel::where('dCaseNo', $this->caseNo)->first();

        if (! $enlistment) {
            throw new \InvalidArgumentException('Enlistment with the provided case number does not exist');
        }

        ProfileModel::create([
            'dw_clientcode' => $this->clientCode,
            'dTransNo' => $this->profileTransNo,
            'en_CaseNo' => $this->caseNo,
            'px_pin' => $enlistment->px_pin,
            'dProfDate' => $clientProfileData['dProfDate'] ?? CarbonImmutable::now()->format('Y-m-d'),
            'dPatientPin' => $enlistment->dPatientPin,
            'patientname' => $enlistment->patientname,
            'dPatientType' => $enlistment->dPatientType,
            'dPatientAge' => $enlistment->dPatientDob ? CarbonImmutable::parse($enlistment->dPatientDob)->age : null,
            'dMemPin' => $enlistment->dMemPin,
            'dEffyear' => $enlistment->dEffyear,
            'dATCode' => $clientProfileData['dATC'] ?? '',
            'dIsWalkedIn' => $clientProfileData['dIsWalkedIn'] ?? '',
            'dTransDate' => CarbonImmutable::now()->format('Y-m-d'),
        ]);
    }

    private function saveProfileDetails(array $profileDetails)
    {
        $profile = ProfileModel::where(['dTransNo' => $this->profileTransNo, 'en_CaseNo' => $this->caseNo])->first();

        if (! $profile) {
            throw new \InvalidArgumentException('Profile record not found for the provided profileTransNo');
        }

        $profile->update([
            'dIsWalkedIn' => $profileDetails['dIsWalkedIn'] ?? $profile->dIsWalkedIn,
            'dATC' => $profileDetails['dATC'] ?? $profile->dATC,
            'dProfDate' => $profileDetails['dProfDate'] ?? $profile->dProfDate,
        ]);
    }

    private function saveMedicalHistory(array $medicalHistory)
    {
        $medhistToSave = [];
        $mhspecificToSave = [];
        foreach ($medicalHistory['chkMedHistDiseases'] as $key => $condition) {
            if (isset($condition['selected'])) {
                $medhistToSave[] = [
                    'dw_clientcode' => $this->clientCode,
                    'px_pin' => $this->pxPin,
                    'p_TransNo' => $this->profileTransNo,
                    'en_caseno' => $this->caseNo,
                    'dMdiseaseCode' => $key,
                ];
                if (isset($condition['specify']) && $condition['specify'] !== null) {
                    $mhspecificToSave[] = [
                        'dw_clientcode' => $this->clientCode,
                        'px_pin' => $this->pxPin,
                        'en_caseno' => $this->caseNo,
                        'p_TransNo' => $this->profileTransNo,
                        'dMdiseaseCode' => $key,
                        'dSpecificDesc' => $condition['specify'],
                    ];
                }
            }
        }
        // Save all collected medical history records
        if (! empty($medhistToSave)) {
            // Delete existing records for the profile before inserting new ones
            ProfileMedHistModel::where('p_TransNo', $this->profileTransNo)->delete();
            ProfileMhSpecificModel::where('p_TransNo', $this->profileTransNo)->delete();
            ProfileMedHistModel::insert($medhistToSave);
            ProfileMhSpecificModel::insert($mhspecificToSave);
        }
    }

    private function saveSurgicalHistory(array $surgicalHistory)
    {
        $surgicalHistoryToSave = [];
        if(isset($surgicalHistory['surgicalHistory'])) {
            foreach ($surgicalHistory['surgicalHistory'] as $surgery) {
                if (isset($surgery['operation']) && isset($surgery['date']) && $surgery['operation'] !== null && $surgery['date'] !== null) {
                    $surgicalHistoryToSave[] = [
                        'dw_clientcode' => $this->clientCode,
                        'px_pin' => $this->pxPin,
                        'p_TransNo' => $this->profileTransNo,
                        'en_caseno' => $this->caseNo,
                        'dSurgDesc' => $surgery['operation'],
                        'dSurgDate' => $surgery['date'],
                    ];
                }
            }
        }
        // Save all collected surgical history records
        if (! empty($surgicalHistoryToSave)) {
            // Delete existing records for the profile before inserting new ones
            ProfileSurgHistModel::where('p_TransNo', $this->profileTransNo)->delete();
            ProfileSurgHistModel::insert($surgicalHistoryToSave);
        }
    }

    private function saveFamilyHistory(array $familyHistory)
    {
        $famhistToSave = [];
        $fhspecificToSave = [];
        foreach ($familyHistory['chkFamHistDiseases'] as $key => $condition) {
            if (isset($condition['selected'])) {
                $famhistToSave[] = [
                    'dw_clientcode' => $this->clientCode,
                    'px_pin' => $this->pxPin,
                    'p_TransNo' => $this->profileTransNo,
                    'en_caseno' => $this->caseNo,
                    'dMdiseaseCode' => $key,
                ];
                if (isset($condition['specify']) && $condition['specify'] !== null) {
                    $fhspecificToSave[] = [
                        'dw_clientcode' => $this->clientCode,
                        'px_pin' => $this->pxPin,
                        'en_caseno' => $this->caseNo,
                        'p_TransNo' => $this->profileTransNo,
                        'dMdiseaseCode' => $key,
                        'dSpecificDesc' => $condition['specify'],
                    ];
                }
            }
        }

        // Save all collected medical history records
        if (! empty($famhistToSave)) {
            // Delete existing records for the profile before inserting new ones
            ProfileFamHistModel::where('p_TransNo', $this->profileTransNo)->delete();
            ProfileFamHistModel::insert($famhistToSave);
        }
        if (! empty($fhspecificToSave)) {
            // Delete existing records for the profile before inserting new ones
            ProfileFhSpecificModel::where('p_TransNo', $this->profileTransNo)->delete();
            ProfileFhSpecificModel::insert($fhspecificToSave);
        }
    }

    private function savePersonalSocialHistory(array $personalSocialHistory)
    {
        $personalSocialHistoryToSave[] = [
            'dw_clientcode' => $this->clientCode,
            'px_pin' => $this->pxPin,
            'p_TransNo' => $this->profileTransNo,
            'en_caseno' => $this->caseNo,
            'dIsSmoker' => $personalSocialHistory['dIsSmoker'] ?? null,
            'dNoCigpk' => $personalSocialHistory['dNoCigpk'] ?? 0,
            'dIsADrinker' => $personalSocialHistory['dIsADrinker'] ?? null,
            'dNoBottles' => $personalSocialHistory['dNoBottles'] ?? 0,
            'dIllDrugUser' => $personalSocialHistory['dIllDrugUser'] ?? null,
            'dIsSexuallyActive' => $personalSocialHistory['dIsSexuallyActive'] ?? null,
        ];

        // Save all collected personal/social history records
        if (! empty($personalSocialHistoryToSave)) {
            ProfileSocHistModel::where('p_TransNo', $this->profileTransNo)->delete();
            ProfileSocHistModel::insert($personalSocialHistoryToSave);
        }
    }

    private function saveImmunization(array $immunizations)
    {
        $immunizationsToSave = [];
        if (isset($immunizations['chkImmChild'])) {
            foreach ($immunizations['chkImmChild'] as $childImmunization) {
                $immunizationsToSave[] = [
                    'dw_clientcode' => $this->clientCode,
                    'px_pin' => $this->pxPin,
                    'p_TransNo' => $this->profileTransNo,
                    'en_caseno' => $this->caseNo,
                    'dChildImmcode' => $childImmunization,
                    'dYoungwImmcode' => '',
                    'dPregwImmcode' => '',
                    'dElderlyImmcode' => '',
                    'dOtherImm' => '',
                ];
            }
        }

        if (isset($immunizations['chkImmAdult'])) {
            foreach ($immunizations['chkImmAdult'] as $adultImmunization) {
                $immunizationsToSave[] = [
                    'dw_clientcode' => $this->clientCode,
                    'px_pin' => $this->pxPin,
                    'p_TransNo' => $this->profileTransNo,
                    'en_caseno' => $this->caseNo,
                    'dChildImmcode' => '',
                    'dYoungwImmcode' => $adultImmunization,
                    'dPregwImmcode' => '',
                    'dElderlyImmcode' => '',
                    'dOtherImm' => '',
                ];
            }
        }

        if (isset($immunizations['chkImmPregnant'])) {
            foreach ($immunizations['chkImmPregnant'] as $pregnantImmunization) {
                $immunizationsToSave[] = [
                    'dw_clientcode' => $this->clientCode,
                    'px_pin' => $this->pxPin,
                    'p_TransNo' => $this->profileTransNo,
                    'en_caseno' => $this->caseNo,
                    'dChildImmcode' => '',
                    'dYoungwImmcode' => '',
                    'dPregwImmcode' => $pregnantImmunization,
                    'dElderlyImmcode' => '',
                    'dOtherImm' => '',
                ];
            }
        }

        if (isset($immunizations['chkImmElderly'])) {
            foreach ($immunizations['chkImmElderly'] as $elderlyImmunization) {
                $immunizationsToSave[] = [
                    'dw_clientcode' => $this->clientCode,
                    'px_pin' => $this->pxPin,
                    'p_TransNo' => $this->profileTransNo,
                    'en_caseno' => $this->caseNo,
                    'dChildImmcode' => '',
                    'dYoungwImmcode' => '',
                    'dPregwImmcode' => '',
                    'dElderlyImmcode' => $elderlyImmunization,
                    'dOtherImm' => '',
                ];
            }
        }

        if (isset($immunizations['dOtherImm'])) {
            if ($immunizations['dOtherImm']) {
                $immunizationsToSave[] = [
                    'dw_clientcode' => $this->clientCode,
                    'px_pin' => $this->pxPin,
                    'p_TransNo' => $this->profileTransNo,
                    'en_caseno' => $this->caseNo,
                    'dChildImmcode' => '',
                    'dYoungwImmcode' => '',
                    'dPregwImmcode' => '',
                    'dElderlyImmcode' => '',
                    'dOtherImm' => $immunizations['dOtherImm'],
                ];
            }
        }

        if (! empty($immunizationsToSave)) {
            // Delete existing records for the profile before inserting new ones
            ProfileImmunizationModel::where('p_TransNo', $this->profileTransNo)->delete();
            ProfileImmunizationModel::insert($immunizationsToSave);
        }
    }

    private function saveMensHistory(array $mensHistory)
    {
        $mensHistoryToSave = [
            'dw_clientcode' => $this->clientCode,
            'px_pin' => $this->pxPin,
            'p_TransNo' => $this->profileTransNo,
            'en_caseno' => $this->caseNo,
            'dMenarchePeriod' => $mensHistory['dMenarchePeriod'] ?? 0,
            'dLastMensPeriod' => $mensHistory['dLastMensPeriod'] ?? '1990-01-01',
            'dPeriodDuration' => $mensHistory['dPeriodDuration'] ?? 0,
            'dMensInterval' => $mensHistory['dMensInterval'] ?? 0,
            'dPadsPerDay' => $mensHistory['dPadsPerDay'] ?? 0,
            'dOnsetSexIc' => $mensHistory['dOnsetSexIc'] ?? 0,
            'dBirthCtrlMethod' => $mensHistory['dBirthCtrlMethod'] ?? null,
            'dIsMenopause' => $mensHistory['dIsMenopause'] ?? null,
            'dMenopauseAge' => $mensHistory['dMenopauseAge'] ?? null,
            'dIsApplicable' => (empty($mensHistory)) ? 'N' : 'Y',
        ];

        // Save all collected menstrual history records
        if (! empty($mensHistoryToSave)) {
            // Delete existing records for the profile before inserting new ones
            ProfileMensHistModel::where('p_TransNo', $this->profileTransNo)->delete();
            ProfileMensHistModel::create($mensHistoryToSave);
        }
    }

    private function savePregHistory(array $pregHistory)
    {
        $pregHistoryToSave[] = [
            'dw_clientcode' => $this->clientCode,
            'px_pin' => $this->pxPin,
            'p_TransNo' => $this->profileTransNo,
            'en_caseno' => $this->caseNo,
            'dPregCnt' => $pregHistory['dPregCnt'] ?? 0,
            'dDeliveryCnt' => $pregHistory['dDeliveryCnt'] ?? 0,
            'dDeliveryTyp' => $pregHistory['dDeliveryTyp'] ?? 'X',
            'dFullTermCnt' => $pregHistory['dFullTermCnt'] ?? 0,
            'dPrematureCnt' => $pregHistory['dPrematureCnt'] ?? 0,
            'dAbortionCnt' => $pregHistory['dAbortionCnt'] ?? 0,
            'dLivChildrenCnt' => $pregHistory['dLivChildrenCnt'] ?? 0,
            'dWFamPlan' => $pregHistory['dWFamPlan'] ?? 'N',
            'dIsApplicable' => (empty($pregHistory)) ? 'N' : 'Y',
            'dWPregIndhyp' => $pregHistory['dWPregIndhyp'] ?? '',
        ];

        // Save all collected pregnancy history records
        if (! empty($pregHistoryToSave)) {
            // Delete existing records for the profile before inserting new ones
            ProfilePregHistModel::where('p_TransNo', $this->profileTransNo)->delete();
            ProfilePregHistModel::insert($pregHistoryToSave);
        }
    }

    private function savePepert(array $pepert)
    {
        $pepertToSave[] = [
            'dw_clientcode' => $this->clientCode,
            'px_pin' => $this->pxPin,
            'p_TransNo' => $this->profileTransNo,
            'en_caseno' => $this->caseNo,
            'dSystolic' => $pepert['dSystolic'] ?? 0,
            'dDiastolic' => $pepert['dDiastolic'] ?? 0,
            'dHr' => $pepert['dHr'] ?? 0,
            'dRr' => $pepert['dRr'] ?? 0,
            'dTemp' => $pepert['dTemp'] ?? 0,
            'dHeight' => $pepert['dHeight'] ?? 0,
            'dWeight' => $pepert['dWeight'] ?? 0,
            'dBMI' => $pepert['dBMI'] ?? 0,
            'dLeftVision' => $pepert['dLeftVision'] ?? null,
            'dRightVision' => $pepert['dRightVision'] ?? null,
            'dLength' => $pepert['dLength'] ?? null,
            'dHeadCirc' => $pepert['dHeadCirc'] ?? null,
            'dSkinfoldThickness' => $pepert['dSkinfoldThickness'] ?? null,
            'dWaist' => $pepert['dWaist'] ?? null,
            'dHip' => $pepert['dHip'] ?? null,
            'dLimbs' => $pepert['dLimbs'] ?? null,
            'dMidUpperArmCirc' => $pepert['dMidUpperArmCirc'] ?? null,
            // dZScore
        ];

        // Save all collected pertinent findings records
        if (! empty($pepertToSave)) {
            // Delete existing records for the profile before inserting new ones
            ProfilePepertModel::where('p_TransNo', $this->profileTransNo)->delete();
            ProfilePepertModel::insert($pepertToSave);
        }
    }

    private function saveBloodType(array $bloodType)
    {
        if (isset($bloodType['dBloodType']) && $bloodType['dBloodType'] !== null) {
            $bloodTypeToSave = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
                'dBloodType' => $bloodType['dBloodType'],
            ];

            // upsert blood type record for the profile
            ProfileBloodTypeModel::updateOrCreate([
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
            ], $bloodTypeToSave);
        }
    }

    private function saveGeneralSurvey(array $generalSurvey)
    {
        if (isset($generalSurvey['dGenSurveyId']) && $generalSurvey['dGenSurveyId'] !== null) {
            $generalSurveyToSave = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
                'dGenSurveyId' => $generalSurvey['dGenSurveyId'],
                'dGenSurveyRem' => $generalSurvey['dGenSurveyRem'] ?? '',
            ];
            ProfilePeGenSurveyModel::updateOrCreate([
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
            ], $generalSurveyToSave);
        }
    }

    private function savePertinentFindings(array $pertinentFindings)
    {
        $peMiscToSave = [];

        foreach ($pertinentFindings['heent'] ?? [] as $heent) {
            $peMiscToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
                'dHeentId' => $heent ?? null,
                'dChestId' => '',
                'dHeartId' => '',
                'dAbdomenId' => '',
                'dGuId' => '',
                'dRectalId' => '',
                'dSkinId' => '',
                'dNeuroId' => '',
            ];
        }

        foreach ($pertinentFindings['chest'] ?? [] as $chest) {
            $peMiscToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
                'dHeentId' => '',
                'dChestId' => $chest ?? '',
                'dHeartId' => '',
                'dAbdomenId' => '',
                'dGuId' => '',
                'dRectalId' => '',
                'dSkinId' => '',
                'dNeuroId' => '',
            ];
        }

        foreach ($pertinentFindings['heart'] ?? [] as $heart) {
            $peMiscToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
                'dHeentId' => '',
                'dChestId' => '',
                'dHeartId' => $heart ?? '',
                'dAbdomenId' => '',
                'dGuId' => '',
                'dRectalId' => '',
                'dSkinId' => '',
                'dNeuroId' => '',
            ];
        }

        foreach ($pertinentFindings['abdomen'] ?? [] as $abdomen) {
            $peMiscToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
                'dHeentId' => '',
                'dChestId' => '',
                'dHeartId' => '',
                'dAbdomenId' => $abdomen ?? '',
                'dGuId' => '',
                'dRectalId' => '',
                'dSkinId' => '',
                'dNeuroId' => '',
            ];
        }

        foreach ($pertinentFindings['dGuId'] ?? [] as $dGu) {
            $peMiscToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
                'dHeentId' => '',
                'dChestId' => '',
                'dHeartId' => '',
                'dAbdomenId' => '',
                'dGuId' => $dGu ?? '',
                'dRectalId' => '',
                'dSkinId' => '',
                'dNeuroId' => '',
            ];
        }

        foreach ($pertinentFindings['rectal'] ?? [] as $rectal) {
            $peMiscToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
                'dHeentId' => '',
                'dChestId' => '',
                'dHeartId' => '',
                'dAbdomenId' => '',
                'dGuId' => '',
                'dRectalId' => $rectal ?? '',
                'dSkinId' => '',
                'dNeuroId' => '',
            ];
        }

        foreach ($pertinentFindings['skinExtremities'] ?? [] as $skinExtremities) {
            $peMiscToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
                'dHeentId' => '',
                'dChestId' => '',
                'dHeartId' => '',
                'dAbdomenId' => '',
                'dGuId' => '',
                'dRectalId' => '',
                'dSkinId' => $skinExtremities ?? '',
                'dNeuroId' => '',
            ];
        }

        foreach ($pertinentFindings['neuro'] ?? [] as $neuro) {
            $peMiscToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
                'dHeentId' => '',
                'dChestId' => '',
                'dHeartId' => '',
                'dAbdomenId' => '',
                'dGuId' => '',
                'dRectalId' => '',
                'dSkinId' => '',
                'dNeuroId' => $neuro ?? '',
            ];
        }

        $peSpecificToSave = [
            'dw_clientcode' => $this->clientCode,
            'px_pin' => $this->pxPin,
            'p_TransNo' => $this->profileTransNo,
            'en_caseno' => $this->caseNo,
            'dSkinRem' => $pertinentFindings['dHeentRem'] ?? null,
            'dHeentRem' => $pertinentFindings['dHeentRem'] ?? null,
            'dChestRem' => $pertinentFindings['dChestRem'] ?? null,
            'dHeartRem' => $pertinentFindings['dHeartRem'] ?? null,
            'dAbdomenRem' => $pertinentFindings['dAbdomenRem'] ?? null,
            'dNeuroRem' => $pertinentFindings['dNeuroRem'] ?? null,
            'dRectalRem' => $pertinentFindings['dRectalRem'] ?? null,
            'dGuRem' => $pertinentFindings['dGuRem'] ?? null,
        ];

        // dd($peMiscToSave, $peSpecificToSave);
        if (! empty($peMiscToSave)) {
            ProfilePeMiscModel::where('p_TransNo', $this->profileTransNo)->delete();
            ProfilePeMiscModel::insert($peMiscToSave);

            ProfilePeSpecificModel::updateOrCreate([
                'p_TransNo' => $this->profileTransNo,
                'en_caseno' => $this->caseNo,
            ], $peSpecificToSave);
        }
    }

    private function saveNCDHighRiskAssessment(array $ncdHighRiskAssessment)
    {
        if(empty($ncdHighRiskAssessment)) {
            return; // Skip saving if the NCD high risk assessment data is empty
        }

        $hasEntry = false;
        foreach ($ncdHighRiskAssessment as $value) {
            if ($value !== null) {
                $hasEntry = true;
                break;
            }
        }
        
        if (!$hasEntry) {
            return; // Skip saving if no valid entries are found
        }

        // dd($ncdHighRiskAssessment);
        $ncdHighRiskAssessmentToSave = [
            'dw_clientcode' => $this->clientCode,
            'px_pin' => $this->pxPin,
            'p_TransNo' => $this->profileTransNo,
            'en_caseno' => $this->caseNo,
            'dQid1_Yn' => $ncdHighRiskAssessment['dQid1_Yn'] ?? null,
            'dQid2_Yn' => $ncdHighRiskAssessment['dQid2_Yn'] ?? null,
            'dQid3_Yn' => $ncdHighRiskAssessment['dQid3_Yn'] ?? null,
            'dQid4_Yn' => $ncdHighRiskAssessment['dQid4_Yn'] ?? null,
            'dQid5_Ynx' => $ncdHighRiskAssessment['dQid5_Ynx'] ?? null,
            'dQid6_Yn' => $ncdHighRiskAssessment['dQid6_Yn'] ?? null,
            'dQid7_Yn' => $ncdHighRiskAssessment['dQid7_Yn'] ?? null,
            'dQid8_Yn' => $ncdHighRiskAssessment['dQid8_Yn'] ?? null,
            'dQid9_Yn' => $ncdHighRiskAssessment['dQid9_Yn'] ?? null,
            'dQid10_Yn' => $ncdHighRiskAssessment['dQid10_Yn'] ?? null,
            'dQid11_Yn' => $ncdHighRiskAssessment['dQid11_Yn'] ?? null,
            'dQid12_Yn' => $ncdHighRiskAssessment['dQid12_Yn'] ?? null,
            'dQid13_Yn' => $ncdHighRiskAssessment['dQid13_Yn'] ?? null,
            'dQid14_Yn' => $ncdHighRiskAssessment['dQid14_Yn'] ?? null,
            'dQid15_Yn' => $ncdHighRiskAssessment['dQid15_Yn'] ?? null,
            'dQid16_Yn' => $ncdHighRiskAssessment['dQid16_Yn'] ?? null,
            'dQid17_abcde' => $ncdHighRiskAssessment['dQid17_abcde'] ?? null,
            'dQid18_Yn' => $ncdHighRiskAssessment['dQid18_Yn'] ?? null,
            'dQid19_Yn' => $ncdHighRiskAssessment['dQid19_Yn'] ?? null,
            'dQid19_Fbsmg' => $ncdHighRiskAssessment['dQid19_Fbsmg'] ?? null,
            'dQid19_Fbsmmol' => $ncdHighRiskAssessment['dQid19_Fbsmmol'] ?? null,
            'dQid19_Fbsdate' => $ncdHighRiskAssessment['dQid19_Fbsdate'] ?? null,
            'dQid20_Yn' => $ncdHighRiskAssessment['dQid20_Yn'] ?? null,
            'dQid20_Choleval' => $ncdHighRiskAssessment['dQid20_Choleval'] ?? null,
            'dQid20_Choledate' => $ncdHighRiskAssessment['dQid20_Choledate'] ?? null,
            'dQid21_Yn' => $ncdHighRiskAssessment['dQid21_Yn'] ?? null,
            'dQid21_Ketonval' => $ncdHighRiskAssessment['dQid21_Ketonval'] ?? null,
            'dQid21_Ketondate' => $ncdHighRiskAssessment['dQid21_Ketondate'] ?? null,
            'dQid22_Yn' => $ncdHighRiskAssessment['dQid22_Yn'] ?? null,
            'dQid22_Proteinval' => $ncdHighRiskAssessment['dQid22_Proteinval'] ?? null,
            'dQid22_Proteindate' => $ncdHighRiskAssessment['dQid22_Proteindate'] ?? null,
            'dQid23_Yn' => $ncdHighRiskAssessment['dQid23_Yn'] ?? null,
            'dQid24_Yn' => $ncdHighRiskAssessment['dQid24_Yn'] ?? null,
        ];

        ProfileNcdQansModel::updateOrCreate([
            'p_TransNo' => $this->profileTransNo,
            'en_caseno' => $this->caseNo,
        ], $ncdHighRiskAssessmentToSave);
    }

    private function generateProfileTransNo(): string
    {
        $acreNo = $this->clientService->getHciAccreditationNumber();
        $yearMont = CarbonImmutable::now()->format('Ym');
        $fiveSeries = fake()->numerify('#####'); // TODO: Implement actual 5 series random number generation logic

        return "P{$acreNo}{$yearMont}{$fiveSeries}";
    }
}
