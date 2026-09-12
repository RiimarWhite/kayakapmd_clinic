<?php

namespace App\Services\YakapManagement;

use App\Models\EnlistmentModel;
use App\Models\Libraries\IcdLibModel;
use App\Models\Libraries\MedicineLibModel;
use App\Models\PhicChargesModel;
use App\Models\SOAPModel;
use App\Models\Soaps\SoapAdviceModel;
use App\Models\Soaps\SoapDiagnosticModel;
use App\Models\Soaps\SoapIcdModel;
use App\Models\Soaps\SoapManagementModel;
use App\Models\Soaps\SoapMedicineModel;
use App\Models\Soaps\SoapPeMiscModel;
use App\Models\Soaps\SoapPepertModel;
use App\Models\Soaps\SoapPeSpecificModel;
use App\Models\Soaps\SoapSubjectiveModel;
use App\Models\Stocks\StocksListingModel;
use App\Models\StocksLedger;
use App\Services\ClientService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SoapService
{
    private $caseNo;

    private $soapTransNo;

    private $pxPin;
    private $clientCode;
    private $consultCode;
    private $enlistment;

    public function __construct(
        private LaboratoryResultService $laboratoryResultService,
        private ClientService $clientService,
        private ConsultationService $consultationService
    ) {
        $this->clientCode = $this->clientService->getClientCode();
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getLaboratoryResultsForSoap(string $transNo): array
    {
        return $this->laboratoryResultService->loadByTransNo($transNo);
    }

    public function getSoapDetails($transNo): SOAPModel
    {
        $soapDetails = SOAPModel::with([
            'advice',
            'diagnostics',
            'icd',
            'management',
            'peMisc',
            'pepert',
            'peSpecific',
            'subjective',
            'phicCharges',
        ])->where('pHciTransNo', $transNo)->first();

        foreach ($soapDetails->icd as $icdRecord) {
            $icdLib = IcdLibModel::where('icd_code', $icdRecord->dIcdCode)->first();
            $icdRecord->icd_desc = $icdLib ? $icdLib->icd_desc : '';
        }

        $soapDetails->diagnosisFromStocksLedger = [];

        if ($soapDetails->px_consultcode_cn) {
            $diagnosisFromStocksLedger = StocksLedger::where('item_grouping', 'DIAGNOSTIC')->where('px_consultcode_cn', $soapDetails->px_consultcode_cn)->get();
            $soapDetails->diagnosisFromStocksLedger = $diagnosisFromStocksLedger;
        }

        return $soapDetails;
    }

    public function saveSoapData(array $data)
    {
        if (! isset($data['enlistmentCaseNo']) || $data['enlistmentCaseNo'] === null) {
            throw new \InvalidArgumentException('Missing required identifiers: enlistmentCaseNo');
        }

        $this->caseNo = $data['enlistmentCaseNo'];

        try {
            $enlistment = EnlistmentModel::where('dCaseNo', $this->caseNo)->first();

            if (! $enlistment) {
                throw new \InvalidArgumentException('Enlistment with the provided case number does not exist');
            }

            $this->enlistment = $enlistment;
            $this->pxPin = $enlistment->px_pin;

            DB::beginTransaction();

            $this->consultCode = $data['clientProfile']['consultCode'];

            if (! isset($data['soapTransNo']) || $data['soapTransNo'] === null) {
                $this->soapTransNo = $this->generateSoapTransNo();
                $this->createSoapRecord($data['clientProfile'] ?? []);
            } else {
                $this->soapTransNo = $data['soapTransNo'];
                $soap = SOAPModel::where('pHciTransNo', $this->soapTransNo)->first();
                $this->updateSoapRecord($soap, $data['clientProfile'] ?? []);
            }


            $this->saveSubjectiveHistoryIllness($data['subjectiveHistory'] ?? []);
            $this->savePertinentFindings($data['objectivePhysicalExamination'] ?? []);
            $this->saveIcdDiagnosis($data['assessmentDiagnosis'] ?? []);
            $this->savePlanManagement($data['planManagement'] ?? []);
            $this->laboratoryResultService->save(
                $this->caseNo,
                $this->soapTransNo,
                $this->pxPin,
                $data['laboratoryResults'] ?? [],
                // $data['planManagement'] ?? [],
                $enlistment,
            );
            $this->saveMedsList($data['medsList'] ?? []);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; // Rethrow the exception after rolling back
        }
    }

    public function generateSoapTransNo(): string
    {
        $acreNo = $this->clientService->getHciAccreditationNumber();
        $yearMont = CarbonImmutable::now()->format('Ym');
        $fiveSeries = fake()->numerify('#####'); // TODO: Implement actual 5 series random number generation logic

        return "S{$acreNo}{$yearMont}{$fiveSeries}";
    }

    private function saveMedsList($medsList)
    {
        if (empty($medsList)) {
            return;
        }

        // Extract all drug codes to avoid N+1 queries
        $drugCodes = array_column($medsList, 'drugCode');

        // Fetch all medicine details at once
        $medsDetailsFromStocks = $this->getMedicineDetailsFromStocksListing($drugCodes);
        $medsDetailsFromLibrary = $this->getMedicineDetailsFromLibrary($drugCodes);

        $medsChargesToSave = [];
        foreach ($medsList as $meds) {
            $drugCode = $meds['drugCode'];
            $medsDetails = $medsDetailsFromLibrary->get($drugCode);
            $medsDetailsStocks = $medsDetailsFromStocks->get($drugCode);

            if (empty($drugCode)) {
                continue;
            }

            $medsChargesToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'px_consultcode_cn' => $this->consultCode,
                'en_caseno' => $this->caseNo,
                's_TransNo' => $this->soapTransNo,
                'pxname' => $this->enlistment->patientname,
                'item_grouping' => 'DRUGS AND MEDS',
                'prod_code' => $medsDetailsStocks->prod_code ?? $meds['prodCode'] ?? '',
                'drug_code' => $drugCode,
                'gen_code' => $medsDetails->GEN_CODE ?? $meds['genCode'] ?? '',
                'salt_code' => $medsDetails->SALT_CODE ?? $meds['saltCode'] ?? '',
                'strength_code' => $medsDetails->STRENGTH_CODE ?? $meds['strengthCode'] ?? '',
                'form_code' => $medsDetails->FORM_CODE ?? $meds['formCode'] ?? '',
                'unit_code' => $medsDetails->UNIT_CODE ?? $meds['unitCode'] ?? '',
                'package_code' => $medsDetails->PACKAGE_CODE ?? $meds['packageCode'] ?? '',
                'generic_name' => $medsDetailsStocks->drug_generic ?? $this->getMedicineCodeDescription('dw_lib_meds_generic', 'gen_code', $medsDetails->GEN_CODE ?? '', 'gen_desc') ?? $meds['genericName'] ?? '',
                'prescribed_quantity' => $meds['instructionQuantity'] ?? null,
                'ins_strength' => $meds['instructionStrength'] ?? 'N/A',
                'ins_frequency' => $meds['instructionFrequency'] ?? 'N/A',
                'qty' => $meds['quantity'] ?? null,
                'unit' => $medsDetailsStocks->unit ?? $this->getMedicineCodeDescription('dw_lib_meds_unit', 'unit_code', $medsDetails->UNIT_CODE ?? '', 'unit_desc') ?? '',
                'actual_price' => $meds['actualPrice'],
                'total_price' => $meds['actualPrice'] * ($meds['quantity'] ?? 1),
                'doc_name' => $meds['physician'] ?? '',
                'is_applicable' => 'Y',
                'is_dispensed' => $meds['isDispensed'] ?? $meds['dispenseDate'] ? 'Y' : 'N',
                'dispensed_date' => $meds['dispenseDate'] ?? null,
                'dispensedby' => $meds['dispensingPersonnel'] ?? '',
                'updated' => CarbonImmutable::now(),
            ];
        }

        foreach ($medsChargesToSave as $medsCharge) {
            PhicChargesModel::updateOrCreate(
                [
                    'dw_clientcode' => $medsCharge['dw_clientcode'],
                    's_TransNo' => $medsCharge['s_TransNo'],
                    'en_caseno' => $medsCharge['en_caseno'],
                    'drug_code' => $medsCharge['drug_code'],
                ],
                $medsCharge
            );
        }

        // Delete medicines that were removed on the client side
        $savedDrugCodes = array_values(array_filter(array_column($medsChargesToSave, 'drug_code')));
        PhicChargesModel::where('dw_clientcode', $this->clientCode)
            ->where('s_TransNo', $this->soapTransNo)
            ->where('en_caseno', $this->caseNo)
            ->where('item_grouping', 'DRUGS AND MEDS')
            ->whereNotIn('drug_code', $savedDrugCodes)
            ->delete();
    }

    private function getMedicineDetailsFromLibrary(array $drugCodes)
    {
        return MedicineLibModel::whereIn('DRUG_CODE', $drugCodes)
            ->get()
            ->keyBy('DRUG_CODE');
    }

    private function getMedicineDetailsFromStocksListing(array $drugCodes)
    {
        return StocksListingModel::whereIn('phic_reference_code', $drugCodes)
            ->get()
            ->keyBy('phic_reference_code');
    }

    private function getMedicineCodeDescription($table, $codeColumn, $code, $descriptionColumn)
    {
        if ($code == '') {
            return null;
        }

        $record = DB::table($table)->where($codeColumn, $code)->first();
        return $record ? $record->$descriptionColumn : null;
    }

    private function savePlanManagement(array $planManagement)
    {
        $reco = $planManagement['diagnostic_doctor_reco'] ?? [];
        $patient = $planManagement['diagnostic_patient'] ?? [];

        $allKeys = array_unique(array_merge(array_keys($reco), array_keys($patient)));

        $result = [];
        foreach ($allKeys as $key) {
            $result[$key] = [
                'reco' => $reco[$key] ?? null,
                'patient' => $patient[$key] ?? null,
            ];
        }

        $planManagementToSave = [];
        foreach ($result as $key => $value) {
            $otherRemarks = '';
            if ($key == '99' && $value['reco'] == 'Y') {
                $otherRemarks = $planManagement['diagnostic_oth_remarks'] ?? '';
            }
            $planManagementToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'px_consultcode_cn' => $this->consultCode,
                's_TransNo' => $this->soapTransNo,
                'en_caseno' => $this->caseNo,
                'dDiagnosticId' => $key,
                'dOthRemarks' => $otherRemarks,
                'dIsPhysicianRecommend' => $value['reco'] ?? 'X',
                'dPatientRemarks' => $value['patient'] ?? 'XX',
            ];
        }

        if (! empty($planManagementToSave)) {
            SoapDiagnosticModel::where('s_TransNo', $this->soapTransNo)->delete();
            SoapDiagnosticModel::insert($planManagementToSave);
        }

        if ($planManagement['dRemarks']) {
            SoapAdviceModel::updateOrCreate([
                's_TransNo' => $this->soapTransNo,
                'en_CaseNo' => $this->caseNo,
            ], [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'px_consultcode_cn' => $this->consultCode,
                's_TransNo' => $this->soapTransNo,
                'en_CaseNo' => $this->caseNo,
                'dRemarks' => $planManagement['dRemarks'],
            ]);
        }

        if (empty($planManagement['management'])) {
            return;
        }

        $managementToSave = [];
        foreach ($planManagement['management'] as $managementId) {
            $otherRemarks = '';
            if ($managementId == 'X') {
                $otherRemarks = $planManagement['management_oth_remarks1'] ?? '';
            }
            $managementToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'px_consultcode_cn' => $this->consultCode,
                's_TransNo' => $this->soapTransNo,
                'en_caseno' => $this->caseNo,
                'pManagementId' => $managementId,
                'pOthRemarks' => $otherRemarks,
            ];
        }

        if (! empty($managementToSave)) {
            SoapManagementModel::where('s_TransNo', $this->soapTransNo)->delete();
            SoapManagementModel::insert($managementToSave);
        }
    }

    private function saveIcdDiagnosis(array $icdDiagnosis)
    {
        if (empty($icdDiagnosis['diagnoses'])) {
            return;
        }

        $icdDataToSave = [];
        foreach ($icdDiagnosis['diagnoses'] as $icd) {
            $icdDataToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'px_consultcode_cn' => $this->consultCode,
                's_TransNo' => $this->soapTransNo,
                'en_caseno' => $this->caseNo,
                'dIcdCode' => $icd,
            ];
        }

        if (! empty($icdDataToSave)) {
            SoapIcdModel::where('s_TransNo', $this->soapTransNo)->delete();
            SoapIcdModel::insert($icdDataToSave);
        }
    }

    private function savePertinentFindings(array $pertinentFindings)
    {
        $peMiscToSave = [];

        foreach ($pertinentFindings['heent'] ?? [] as $heent) {
            $peMiscToSave[] = [
                'dw_clientcode' => $this->clientCode,
                'px_pin' => $this->pxPin,
                'px_consultcode_cn' => $this->consultCode,
                's_TransNo' => $this->soapTransNo,
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
                'px_consultcode_cn' => $this->consultCode,
                's_TransNo' => $this->soapTransNo,
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
                'px_consultcode_cn' => $this->consultCode,
                's_TransNo' => $this->soapTransNo,
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
                'px_consultcode_cn' => $this->consultCode,
                's_TransNo' => $this->soapTransNo,
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
                'px_consultcode_cn' => $this->consultCode,
                's_TransNo' => $this->soapTransNo,
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
                'px_consultcode_cn' => $this->consultCode,
                's_TransNo' => $this->soapTransNo,
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
                'px_consultcode_cn' => $this->consultCode,
                's_TransNo' => $this->soapTransNo,
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
                'px_consultcode_cn' => $this->consultCode,
                's_TransNo' => $this->soapTransNo,
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
            'px_consultcode_cn' => $this->consultCode,
            's_TransNo' => $this->soapTransNo,
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

        // dd($peMiscToSave);
        if (! empty($peMiscToSave)) {
            SoapPeMiscModel::where('s_TransNo', $this->soapTransNo)->delete();
            SoapPeMiscModel::insert($peMiscToSave);

            SoapPeSpecificModel::updateOrCreate([
                's_TransNo' => $this->soapTransNo,
                'en_caseno' => $this->caseNo,
            ], $peSpecificToSave);
        }

        // Pepert
        $pepertToSave[] = [
            'dw_clientcode' => $this->clientCode,
            'px_pin' => $this->pxPin,
            'px_consultcode_cn' => $this->consultCode,
            's_TransNo' => $this->soapTransNo,
            'en_caseno' => $this->caseNo,
            'dSystolic' => $pertinentFindings['dSystolicSoap'] ?? 0,
            'dDiastolic' => $pertinentFindings['dDiastolicSoap'] ?? 0,
            'dHr' => $pertinentFindings['dHrSoap'] ?? 0,
            'dRr' => $pertinentFindings['dRrSoap'] ?? 0,
            'dTemp' => $pertinentFindings['dTempSoap'] ?? 0,
            'dHeight' => $pertinentFindings['dHeightSoap'] ?? 0,
            'dWeight' => $pertinentFindings['dWeightSoap'] ?? 0,
            'dBMI' => $pertinentFindings['dBMISoap'] ?? 0,
            'dLeftVision' => $pertinentFindings['dLeftVisionSoap'] ?? null,
            'dRightVision' => $pertinentFindings['dRightVisionSoap'] ?? null,
            'dLength' => $pertinentFindings['dLengthSoap'] ?? null,
            'dHeadCirc' => $pertinentFindings['dHeadCircSoap'] ?? null,
            'dSkinfoldThickness' => $pertinentFindings['dSkinfoldThicknessSoap'] ?? null,
            'dWaist' => $pertinentFindings['dWaistSoap'] ?? null,
            'dHip' => $pertinentFindings['dHipSoap'] ?? null,
            'dLimbs' => $pertinentFindings['dLimbsSoap'] ?? null,
            'dMidUpperArmCirc' => $pertinentFindings['dMidUpperArmCircSoap'] ?? null,
            // dZScore
        ];

        // Save all collected pertinent findings records
        if (! empty($pepertToSave)) {
            // Delete existing records for the profile before inserting new ones
            SoapPepertModel::where('s_TransNo', $this->soapTransNo)->delete();
            SoapPepertModel::insert($pepertToSave);
        }
    }

    private function saveSubjectiveHistoryIllness(array $subjectiveData)
    {
        if (isset($subjectiveData['signsSymptoms'])) {
            $dSignsSymptoms = implode(';', $subjectiveData['signsSymptoms']);
        } else {
            $dSignsSymptoms = '';
        }

        if (empty($subjectiveData['signsSymptoms']) || ! isset($subjectiveData['signsSymptoms']) && $subjectiveData['dIllnessHistory']) {
            return;
        }

        $subjectiveDataToSave = [
            'dw_clientcode' => $this->clientCode,
            'px_pin' => $this->pxPin,
            'px_consultcode_cn' => $this->consultCode,
            's_TransNo' => $this->soapTransNo,
            'en_caseno' => $this->caseNo,
            'chifcomplaint' => '', // TODO
            'dIllnessHistory' => $subjectiveData['dIllnessHistory'],
            'dSignsSymptoms' => $dSignsSymptoms,
            'dOtherComplaint' => $subjectiveData['dOtherComplaint'],
            'dPainSite' => $subjectiveData['dPainSite'],
        ];

        SoapSubjectiveModel::updateOrCreate([
            's_TransNo' => $this->soapTransNo,
            'en_caseno' => $this->caseNo,
        ], $subjectiveDataToSave);
    }

    private function updateSoapRecord($soap, array $clientSoapData)
    {
        $affected = $soap->update([
            'dSoapDate' => $clientSoapData['dSoapDate'] ?? null,
            'dATC' => $clientSoapData['dATC'] ?? null,
            'dIsWalkedIn' => $clientSoapData['dIsWalkedIn'] ?? null,
            'dCoPay' => $clientSoapData['dCoPay'] ?? null,
        ]);

        if ($affected === 0) {
            Log::warning("updateSoapRecord: no rows updated for pHciTransNo={$this->soapTransNo}");
        }
    }

    private function createSoapRecord($clientSoapData)
    {
        // dd($clientSoapData);
        $enlistment = EnlistmentModel::where('dCaseNo', $this->caseNo)->first();

        if (! $enlistment) {
            throw new \InvalidArgumentException('Enlistment with the provided case number does not exist');
        }

        $yearNow = CarbonImmutable::now()->year;
        $consultations = SOAPModel::where('en_CaseNo', $this->caseNo)->whereYear('dSoapDate', $yearNow)->get();

        SOAPModel::create([
            'pHciTransNo' => $this->soapTransNo,
            'px_pin' => $enlistment->px_pin,
            'px_consultcode_cn' => $clientSoapData['consultCode'],
            'en_CaseNo' => $this->caseNo,
            'dSoapDate' => $clientSoapData['dSoapDate'],
            'dPatientPin' => $enlistment->dPatientPin,
            'dPatientType' => $enlistment->dPatientType,
            'dMemPin' => $enlistment->dMemPin,
            'dEffYear' => $enlistment->dEffyear,
            'dATC' => $clientSoapData['dATC'] ?? '',
            'dIsWalkedIn' => $clientSoapData['dIsWalkedIn'] ?? '',
            'dCoPay' => $clientSoapData['dCoPay'] ?? '',
            'dTransDate' => CarbonImmutable::now()->format('Y-m-d'),
            'visit_count_year' => $consultations->count() + 1
        ]);
    }

    public function getSoapDetailsForEkasReport($transNo): SOAPModel
    {
        $soapDetails = SOAPModel::with([
            'enlistment',
            'diagnostics.diagnosticDescription'
        ])->where('pHciTransNo', $transNo)->first();

        return $soapDetails;
    }

    public function getSoapDetailsEpressForReport($transNo): SOAPModel
    {
        $soapDetails = SOAPModel::with([
            'phicCharges.medicineDetails',
            'enlistment'
        ])->where('pHciTransNo', $transNo)->first();

        return $soapDetails;
    }
}
