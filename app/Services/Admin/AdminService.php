<?php

namespace App\Services\Admin;

use App\Models\PCBModel;
use App\Models\EnlistmentModel;
use App\Models\SOAPModel;
use App\Models\Stocks\StocksLedgerModel;
use App\Models\Stocks\StocksListingModel;
use App\Models\XmlTransModel;
use DOMElement;
use Illuminate\Support\Facades\DB;
use DOMDocument;
use function Symfony\Component\Clock\now;

class AdminService
{
    public function getStockList(array $filter)
    {
        $start = $filter['start'] ?? 0;
        $length = $filter['length'] ?? 25;
        $search = $filter['search']['value'];

        $query = StocksListingModel::query();
        $recordsTotal = $query->count();

        if (!empty($search)) {
            $query->where('prod_itemdscr', 'like', "%{$search}%");
        }

        $recordsFiltered = $query->count();

        $items = $query->select(['prodcode', 'prod_itemdscr', 'item_grouping', 'phic_reference_code', 'price_regular', 'price_phic', 'price_hmo', 'price_others'])
            ->orderBy('prod_itemdscr')
            ->offset($start)
            ->limit($length)
            ->get();

        return [
            'draw' => intval($filter['draw']),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $items
        ];
    }

    public function getDrugRefs(?string $filter)
    {
        $generic = DB::table('dw_lib_meds_generic')
            ->select([
                'gen_desc as label',
                'gen_desc as value',
                'gen_code as id'
            ])
            ->when($filter, function ($query, $filter) {
                $query->where('gen_desc', 'like', "%{$filter}%");
            })
            ->get();

        return [ 'generic' => $generic ];
    }

    public function getDiagsRefs(?string $filter)
    {
        $diagnostics = DB::table('dw_lib_diagnostic')
            ->select([
                'diagnostic_desc as label',
                'diagnostic_id as value',
                'diagnostic_id as id'
            ])
            ->when($filter, function ($query, $filter) {
                $query->where('diagnostic_desc', 'like', "%{$filter}%");
            })
            ->get();

            return ['diagnostic' => $diagnostics];
    }

    public function saveXML(array $data)
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        return 'A';
    }

    public function generateXML(array $data, string $session)
    {
        $doc = new DOMDocument();
        $doc->formatOutput = true;

        $profile = $this->getProfile($session);

        $pcbElement = $this->buildPCBElement($doc);
        $enlistData = $this->getEnlistData(
            is_object($data['data'][0]) ? collect($data['data'])->pluck('px_pin')->toArray() : array_column($data['data'], 'px_pin'),
            $data['start'],
            $data['end']
        );

        if ($data['type'] == 'second') {
            $case = array_column($data['data'], 'en_CaseNo');

            $count = SOAPModel::whereIn('en_CaseNo', $case)
                ->whereNotNull('report_code')
                ->where('report_code', '!=', '')
                ->count();

                if ($count !== count($case)) {
                    return ['success' => false, 'message' => 'One or more records does not have a tranche 1 report yet.'];
                }
        }

        if (!$enlistData) return ['success' => false, 'message' => 'Patient not found.'];

        switch ($data['type']) {
            case 'first':
                foreach ($enlistData as $enlData) {
                    $enlistmentsWrapper = $this->buildEnlistments($doc, $enlData);
                    $profilingContainer = $this->buildProfiling($doc, $enlData->profiles);
                    $soapWrapper = $this->buildSOAP($doc, $enlData->soaps);
                    $diagnosticWrapper = $this->buildDiagnosticExamResult($doc, $enlData->diagExamResults, false);
                    $medicineWrapper = $this->buildMedicines($doc, $enlData->meds);

                    $pcbElement->appendChild($enlistmentsWrapper);
                    $pcbElement->appendChild($profilingContainer);
                    $pcbElement->appendChild($soapWrapper);
                    $pcbElement->appendChild($diagnosticWrapper);
                    $pcbElement->appendChild($medicineWrapper);
                }
                break;
            case 'second':
                foreach ($enlistData as $enlData) {
                    $enlistmentsWrapper = $this->buildEnlistments($doc, $enlData);
                    $profilingContainer = $this->buildProfiling($doc, $enlData->profiles);
                    $soapWrapper = $this->buildSOAP($doc, $enlData->soaps);
                    $diagnosticWrapper = $this->buildDiagnosticExamResult($doc, $enlData->diagExamResults, 'second');
                    $medicineWrapper = $this->buildMedicines($doc, $enlData->meds);

                    $pcbElement->appendChild($enlistmentsWrapper);
                    $pcbElement->appendChild($profilingContainer);
                    $pcbElement->appendChild($soapWrapper);
                    $pcbElement->appendChild($diagnosticWrapper);
                    $pcbElement->appendChild($medicineWrapper);
                }
                break;
        }

        $doc->appendChild($pcbElement);

        $reportCode = 'REP' . now()->format('mdYHis');

        foreach ($data['data'] as $dt) {
            $record = SOAPModel::where('en_CaseNo', $dt['en_CaseNo'])->first();

            if ($record) {
                if ($data['type'] == 'first') {
                    $record->update([
                        'report_code' => $reportCode,
                        'report_date' => now()
                    ]);
                } else if ($data['type'] == 'second') {
                    $record->update([
                        'report_code2' => $reportCode,
                        'report_date2' => now()
                    ]);
                }
            }
        }

        $document = $doc->saveXML($doc->documentElement);

        // $passphrase = "THIS_IS_A_PASSWORD";
        // $hashedPw = hash('sha256', $passphrase, true);
        // $key = str_pad(substr($hashedPw, 0, 32), 32, "\0");

        // $iv = random_bytes(128);
        // $finalIv = substr($iv, 0, 16);

        // $encrypted = openssl_encrypt($document, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $finalIv);
        // $payload = json_encode([
        //     'docMimeType' => '<text/xml>',
        //     'hash' => hash('sha256', $document),
        //     'key1' => '',
        //     'key2' => '',
        //     'iv' => base64_encode($iv),
        //     'doc' => base64_encode($encrypted),
        // ], JSON_UNESCAPED_SLASHES);

        $data = [
            'dw_clientcode' => $session,
            'accre_no' => $profile->hciaccreno,
            'report_code' => $reportCode,
            'trans_type' => is_array($data['data']) ? 'BATCH' : 'SINGLE',
            'tanche_type' => $data['type'] == 'first' ? 'FIRST' : 'SECOND',
            'date_range_start' => $data['start'] ?? now(),
            'date_range_end' => $data['end'] ?? now(),
            'date_generated' => now(),
            'XML_CONTENT' => $document,
            'status' => 'PENDING'
        ];

        // Mark data as reported in the database
        XmlTransModel::create($data);

        return ['success' => true, 'document' => $document];
    }

    public function encryptXML(array $data)
    {
        $passphrase = $data['passphrase']; // From PhilHealth
        $hashedPass = hash('sha256', $passphrase, true);
        $key = str_pad(substr($hashedPass, 0, 32), 32, "\0");

        $iv = random_bytes(128);
        $finalIv = substr($iv, 0, 16);

        $encrypted = openssl_encrypt($data['document'], 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $finalIv);
        $payload = json_encode([
            'docMimeType' => '<text/xml>',
            'hash' => hash('sha256', $data['document']),
            'key1' => '',
            'key2' => '',
            'iv' => base64_encode($iv),
            'doc' => base64_encode($encrypted)
        ], JSON_UNESCAPED_SLASHES);

        $record = XmlTransModel::where(['report_code' => $data['report_code']])
            ->update([
                'ENCRYPTED_CONTENT' => $payload
            ]);

        if ($record) {
            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }

    private function getProfile($clientcode)
    {
        return PCBModel::where(['clientcode' => $clientcode])->first();
    }

    private function buildPCBElement(DOMDocument $doc)
    {
        $pcb = PCBModel::first();
        $pcbElement = $doc->createElement('PCB');
        $pcbFields = [
            'pUsername' => $pcb->userid ?? '',
            'pPassword' => $pcb->passwd ?? '',
            'pHciAccreno' => $pcb->hciaccreno ?? '',
            'pPMCCNo' => $pcb->pmccno ?? '',
            'pEnlistTotalCnt' => $pcb->enlistTotalcnt ?? '',
            'pProfileTotalCnt' => $pcb->profileTotalcnt ?? '',
            'pSoapTotalCnt' => $pcb->soapTotalcnt ?? '',
            'pCertificationId' => $pcb->certificationid ?? '',
            'pHciTransmittalNumber' => $pcb->hcitransmittalnumber ?? ''
        ];
        $this->setAttributesFromArray($pcbElement, $pcbFields);
        return $pcbElement;
    }

    private function getEnlistData($pincode, $start, $end)
    {
        return EnlistmentModel::whereIn('px_pin', (array)$pincode)
            // ->whereHas('soaps', function ($q) use ($start, $end) {
            //     $q->whereBetween('dSoapDate', [$start, $end]);
            // })
            ->with([
                // 'soaps' => function ($q) use ($start, $end) {
                //     $q->whereBetween('dSoapDate', [$start, $end]);
                // },
                'profiles' => function ($q) {
                    $q->with([
                        'medHist', 'mhSpecific', 'surgHist',
                        'famHist', 'fhSpecific', 'socHist',
                        'immunizations', 'mensHist', 'pregHist',
                        'pepert', 'bloodTypes', 'peGenSurvey',
                        'peMisc', 'peSpecific', 'ncdQans'
                    ]);
                },
                'diagExamResults',
                'meds'
            ])
            ->get();
    }

    private function buildEnlistments(DOMDocument $doc, $enlistData)
    {
        $enlistmentsWrapper = $doc->createElement('ENLISTMENTS');
        $enlistEntry = $doc->createElement('ENLISTMENT');
        $enlistFields = [
            'pHciCaseNo' => $enlistData->dCaseNo, 'pHciTransNo' => $enlistData->dTransNo,
            'pEffYear' => $enlistData->dEffyear, 'pEnlistStat' => $enlistData->dEnlistStat,
            'pEnlistDate' => $enlistData->dEnlistDate, 'pPackageType' => $enlistData->dPackageType,
            'pMemPin' => $enlistData->dMemPin, 'pMemFname' => $enlistData->dMemFname,
            'pMemMname' => $enlistData->dMemMname, 'pMemLname' => $enlistData->dMemLname,
            'pMemExtname' => $enlistData->dMemExtname, 'pMemDob' => $enlistData->dMemDob,
            'pPatientPin' => $enlistData->dPatientPin, 'pPatientFname' => $enlistData->dPatientFname,
            'pPatientMname' => $enlistData->dPatientMname, 'pPatientLname' => $enlistData->dPatientLname,
            'pPatientExtname' => $enlistData->dPatientExtname, 'pPatientSex' => $enlistData->dPatientSex,
            'pPatientDob' => $enlistData->dPatientDob, 'pPatientType' => $enlistData->dPatientType,
            'pPatientMobileNo' => $enlistData->dPatientMobileNo, 'pPatientLandlineNo' => $enlistData->dPatientLandlineNo,
            'pWithConsent' => $enlistData->dWithConsent, 'pTransDate' => $enlistData->dTransDate,
            'pCreatedBy' => $enlistData->dCreatedBy, 'pReportStatus' => $enlistData->dReportStatus ?? 'U',
            'pDeficiencyRemarks' => $enlistData->dDeficiencyRemarks,
        ];
        $this->setAttributesFromArray($enlistEntry, $enlistFields);
        $enlistmentsWrapper->appendChild($enlistEntry);
        return $enlistmentsWrapper;
    }

    // PROFILE
    private function buildProfiling(DOMDocument $doc, $profiles)
    {
        $profilingWrapper = $doc->createElement('PROFILING');
        $profilingContainer = $doc->createElement('PROFILE');

        $defaultAttributes = [
            'pHciTransNo' => '',
            'pHciCaseNo' => '',
            'pProfDate' => '',
            'pPatientPin' => '',
            'pPatientType' => '',
            'pPatientAge' => '',
            'pMemPin' => '',
            'pEffYear' => '',
            'pATC' => '',
            'pIsWalkedIn' => '',
            'pTransDate' => '',
            'pReportStatus' => 'U',
            'pDeficiencyRemarks' => ''
        ];
        $this->setAttributesFromArray($profilingContainer, $defaultAttributes);

        $mhsWrapper = $doc->createElement('MEDHISTS');
        $mssWrapper = $doc->createElement('MHSPECIFICS');
        $sgsWrapper = $doc->createElement('SURGHISTS');
        $fmsWrapper = $doc->createElement('FAMHISTS');
        $fssWrapper = $doc->createElement('FHSPECIFICS');
        $scElement = $doc->createElement('SOCHIST');
        $immWrapper = $doc->createElement('IMMUNIZATIONS');
        $menWrapper = $doc->createElement('MENHIST');
        $prgWrapper = $doc->createElement('PREGHIST');
        $pepWrapper = $doc->createElement('PEPERT');
        $btElement = $doc->createElement('BLOODTYPE');
        $pegElement = $doc->createElement('PEGENSURVEY');
        $pemWrapper = $doc->createElement('PEMISCS');
        $pesElement = $doc->createElement('PESPECIFIC');
        $ncdqElement = $doc->createElement('NCDQANS');

        foreach ($profiles as $prof) {
            $profileEntry = [
                'pHciTransNo' => $prof->dTransNo ?? '', 'pHciCaseNo' => $prof->en_CaseNo ?? '',
                'pProfDate' => $prof->dProfDate ?? '', 'pPatientPin' => $prof->dPatientPin ?? '',
                'pPatientType' => $prof->dPatientType ?? '', 'pPatientAge' => $prof->dPatientAge ?? '',
                'pMemPin' => $prof->dMemPin ?? '', 'pEffYear' => $prof->dEffyear ?? '',
                'pATC' => $prof->dATC ?? '', 'pIsWalkedIn' => $prof->dIsWalkedIn ?? '',
                'pTransDate' => $prof->dTransDate ?? '', 'pReportStatus' => $prof->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $prof->dDeficiencyRemarks ?? ''
            ];
            $this->setAttributesFromArray($profilingContainer, $profileEntry);

            $this->buildMedicalHistory($doc, $mhsWrapper, $prof->medHist);
            $this->buildMhSpecifics($doc, $mssWrapper, $prof->mhSpecific);
            $this->buildSurgicalHistory($doc, $sgsWrapper, $prof->surgHist);
            $this->buildFamilyHistory($doc, $fmsWrapper, $prof->famHist);
            $this->buildFhSpecifics($doc, $fssWrapper, $prof->fhSpecific);
            $this->buildSocialHistory($doc, $scElement, $prof->socHist->first());
            $this->buildImmunizations($doc, $immWrapper, $prof->immunizations);
            $this->buildMenstrualHistory($doc, $menWrapper, $prof->mensHist->first());
            $this->buildPregnancyHistory($doc, $prgWrapper, $prof->pregHist->first());
            $this->buildPhysicalExam($doc, $profilingContainer, $prof->pepert->first());
            $this->buildBloodType($btElement, $prof->bloodTypes->first());
            $this->buildGeneralSurvey($pegElement, $prof->peGenSurvey->first());
            $this->buildPeMisc($doc, $pemWrapper, $prof->peMisc);
            $this->buildPeSpecific($pesElement, $prof->peSpecific->first());
            $this->buildNcdQans($ncdqElement, $prof->ncdQans->first());
        }

        $profilingContainer->appendChild($mhsWrapper);
        $profilingContainer->appendChild($mssWrapper);
        $profilingContainer->appendChild($sgsWrapper);
        $profilingContainer->appendChild($fmsWrapper);
        $profilingContainer->appendChild($fssWrapper);
        $profilingContainer->appendChild($scElement);
        $profilingContainer->appendChild($immWrapper);
        $profilingContainer->appendChild($menWrapper);
        $profilingContainer->appendChild($prgWrapper);
        $profilingContainer->appendChild($pepWrapper);
        $profilingContainer->appendChild($btElement);
        $profilingContainer->appendChild($pegElement);
        $profilingContainer->appendChild($pemWrapper);
        $profilingContainer->appendChild($pesElement);
        $profilingContainer->appendChild($ncdqElement);

        $profilingWrapper->appendChild($profilingContainer);

        return $profilingWrapper;
    }

    private function buildMedicalHistory(DOMDocument $doc, DOMElement $wrapper, $medHist)
    {
        foreach ($medHist as $med) {
            $mh = $doc->createElement('MEDHIST');
            $this->setAttributesFromArray($mh, [
                'pMdiseaseCode' => $med->dMdiseaseCode ?? '',
                'pReportStatus' => $med->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $med->dDeficiencyRemarks ?? '',
            ]);
            $wrapper->appendChild($mh);
        }
    }

    private function buildMhSpecifics(DOMDocument $doc, DOMElement $wrapper, $mhSpecific)
    {
        foreach ($mhSpecific as $mhSpec) {
            $ms = $doc->createElement('MHSPECIFIC');
            $this->setAttributesFromArray($ms, [
                'pMdiseaseCode' => $mhSpec->dMdiseaseCode ?? '',
                'pSpecificDesc' => $mhSpec->dSpecificDesc ?? '',
                'pReportStatus' => $mhSpec->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $mhSpec->dDeficiencyRemarks ?? '',
            ]);
            $wrapper->appendChild($ms);
        }
    }

    private function buildSurgicalHistory(DOMDocument $doc, DOMElement $wrapper, $surgHist)
    {
        foreach ($surgHist as $surg) {
            $sg = $doc->createElement('SURGHIST');
            $this->setAttributesFromArray($sg, [
                'pSurgDesc' => $surg->dSurgDesc ?? '',
                'pSurgDate' => $surg->dSurgDate ?? '',
                'pReportStatus' => $surg->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $surg->dDeficiencyRemarks ?? '',
            ]);
            $wrapper->appendChild($sg);
        }
    }

    private function buildFamilyHistory(DOMDocument $doc, DOMElement $wrapper, $famHist)
    {
        foreach ($famHist as $fam) {
            $fm = $doc->createElement('FAMHIST');
            $this->setAttributesFromArray($fm, [
                'pMdiseaseCode' => $fam->dMdiseaseCode ?? '',
                'pReportStatus' => $fam->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $fam->dDeficiencyRemarks ?? '',
            ]);
            $wrapper->appendChild($fm);
        }
    }

    private function buildFhSpecifics(DOMDocument $doc, DOMElement $wrapper, $fhSpecific)
    {
        foreach ($fhSpecific as $fSpec) {
            $fs = $doc->createElement('FHSPECIFIC');
            $this->setAttributesFromArray($fs, [
                'pMdiseaseCode' => $fSpec->dMdiseaseCode ?? '',
                'pSpecificDesc' => $fSpec->dSpecificDesc ?? '',
                'pReportStatus' => $fSpec->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $fSpec->dDeficiencyRemarks ?? '',
            ]);
            $wrapper->appendChild($fs);
        }
    }

    private function buildSocialHistory(DOMDocument $doc, DOMElement $element, $socModel)
    {
        if ($socModel) {
            $this->setAttributesFromArray($element, [
                'pIsSmoker' => $socModel->dIsSmoker,
                'pNoCigpk' => $socModel->dNoCigpk,
                'pIsAdrinker' => $socModel->dIsAdrinker,
                'pNoBottles' => $socModel->dNoBottles,
                'pIllDrugUser' => $socModel->dIllDrugUser,
                'pIsSexuallyActive' => $socModel->dIsSexuallyActive,
                'pReportStatus' => $socModel->dReportStatus,
                'pDeficiencyRemarks' => $socModel->dDeficiencyRemarks
            ]);
        }
    }

    private function buildImmunizations(DOMDocument $doc, DOMElement $wrapper, $immunizations)
    {
        foreach ($immunizations as $imu) {
            $im = $doc->createElement('IMMUNIZATION');
            $this->setAttributesFromArray($im, [
                'pChildImmcode' => $imu->dChildImmcode ?? '',
                'pYoungImmcode' => $imu->dYoungImmcode ?? '',
                'pPregwImmcode' => $imu->dPregwImmcode ?? '',
                'pElderlyImmcode' => $imu->dOtherImm ?? '',
                'pReportStatus' => $imu->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $imu->dDeficiencyRemarks ?? '',
            ]);
            $wrapper->appendChild($im);
        }
    }

    private function buildMenstrualHistory(DOMDocument $doc, DOMElement $wrapper, $mensModel)
    {
        if ($mensModel) {
            $mns = $doc->createElement('MENSHIST');
            $this->setAttributesFromArray($mns, [
                'pMenarchePeriod' => $mensModel->dMenarchePeriod,
                'pLastMensPeriod' => $mensModel->dLastMensPeriod,
                'pPeriodDuration' => $mensModel->dPeriodDuration,
                'pMensInterval' => $mensModel->dMensInterval,
                'pPadsPerDay' => $mensModel->dPadsPerDay,
                'pOnsetSexIc' => $mensModel->dOnsetSexIc,
                'pBirthCtrlMethod' => $mensModel->dBirthCtrlMethod,
                'pIsMenopause' => $mensModel->dIsMenopause,
                'pMenopauseAge' => $mensModel->dMenopauseAge,
                'pIsApplicable' => $mensModel->dIsApplicable ?? 'N',
                'pReportStatus' => $mensModel->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $mensModel->dDeficiencyRemarks
            ]);
            $wrapper->appendChild($mns);
        }
    }

    private function buildPregnancyHistory(DOMDocument $doc, DOMElement $wrapper, $pregModel)
    {
        if ($pregModel) {
            $prg = $doc->createElement('PREGHIST');
            $this->setAttributesFromArray($prg, [
                'pPregCnt' => $pregModel->dPregCnt,
                'pDeliveryCnt' => $pregModel->dDeliveryCnt,
                'pDeliveryTyp' => $pregModel->dDeliveryTyp,
                'pFullTermCnt' => $pregModel->dFullTermCnt,
                'pPrematureCnt' => $pregModel->dPrematureCnt,
                'pAbortionCnt' => $pregModel->dAbortionCnt,
                'pLivChildrenCnt' => $pregModel->dLivChildrenCnt,
                'pWPregIndhyp' => $pregModel->dWPregIndhyp,
                'pWFamPlan' => $pregModel->dWFamPlan,
                'pIsApplicable' => $pregModel->dIsApplicable ?? 'N',
                'pReportStatus' => $pregModel->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $pregModel->dDeficiencyRemarks
            ]);
            $wrapper->appendChild($prg);
        }
    }

    private function buildPhysicalExam(DOMDocument $doc, DOMElement $container, $peModel)
    {
        if ($peModel) {
            $pert = $doc->createElement('PEPERT');
            $this->setAttributesFromArray($pert, [
                'pSystolic' => $peModel->dSystolic,
                'pDiastolic' => $peModel->dDiastolic,
                'pHr' => $peModel->dHr,
                'pRr' => $peModel->dRr,
                'pTemp' => $peModel->dTemp,
                'pHeight' => $peModel->dHeight,
                'pWeight' => $peModel->dWeight,
                'pBMI' => $peModel->dBmi,
                'pZScore' => $peModel->dZscore,
                'pLeftVision' => $peModel->dLeftVision,
                'pRightVision' => $peModel->dRightVision,
                'pLength' => $peModel->dLength,
                'pHeadCirc' => $peModel->dHeadCirc,
                'pSkinfoldThickness' => $peModel->dSkinfoldThickness,
                'pWaist' => $peModel->dWaist,
                'pHip' => $peModel->dHip,
                'pLimbs' => $peModel->dLimbs,
                'pMidUpperArmCirc' => $peModel->dMidUpperArmCirc,
                'pReportStatus' => $peModel->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $peModel->dDeficiencyRemarks
            ]);
            $container->appendChild($pert);
        }
    }

    private function buildBloodType(DOMElement $element, $btModel)
    {
        if ($btModel) {
            $this->setAttributesFromArray($element, [
                'pBloodType' => $btModel->dBloodType ?? '',
                'pReportStatus' => $btModel->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $btModel->dDeficiencyRemarks ?? '',
            ]);
            $element->appendChild($element);
        }

        return $element;
    }

    private function buildGeneralSurvey(DOMElement $element, $gsModel)
    {
        if ($gsModel) {
            $this->setAttributesFromArray($element, [
                'pGenSurveyId' => $gsModel->dGenSurveyId ?? '',
                'pGenSurveyRem' => $gsModel->dGenSurveyRem ?? '',
                'pReportStatus' => $gsModel->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $gsModel->dDeficiencyRemarks ?? '',
            ]);
        }
    }

    private function buildPeMisc(DOMDocument $doc, DOMElement $wrapper, $peMisc)
    {
        if ($peMisc && $peMisc->count() > 0) {
            foreach ($peMisc as $misc) {
                $pemisc = $doc->createElement('PEMISC');
                $this->setAttributesFromArray($pemisc, [
                    'pSkinId' => $misc->dSkinId ?? '',
                    'pHeentId' => $misc->dHeentId ?? '',
                    'pChestId' => $misc->dChestId ?? '',
                    'pHeartId' => $misc->dHeartId ?? '',
                    'pAbdomenId' => $misc->dAbdomenId ?? '',
                    'pNeuroId' => $misc->dNeuroId ?? '',
                    'pRectalId' => $misc->dRectalId ?? '',
                    'pGuId' => $misc->dGuId ?? '',
                    'pReportStatus' => $misc->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $misc->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($pemisc);
            }
        }
    }

    private function buildPeSpecific(DOMElement $element, $psModel)
    {
        if ($psModel) {
            $this->setAttributesFromArray($element, [
                'pSkinRem' => $psModel->dSkinRem ?? '',
                'pHeentRem' => $psModel->dHeentRem ?? '',
                'pChestRem' => $psModel->dChestRem ?? '',
                'pHeartRem' => $psModel->dHeartRem ?? '',
                'pAbdomenRem' => $psModel->dAbdomenRem ?? '',
                'pNeuroRem' => $psModel->dNeuroRem ?? '',
                'pRectalRem' => $psModel->dRectalRem ?? '',
                'pGuRem' => $psModel->dGuRem ?? '',
                'pReportStatus' => $psModel->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $psModel->dDeficiencyRemarks ?? '',
            ]);
        }
    }

    private function buildNcdQans(DOMElement $element, $ncdModel)
    {
        if ($ncdModel) {
            for ($i = 1; $i <= 16; $i++) {
                $dbField = "dQid{$i}_Yn";
                $element->setAttribute("pQid{$i}_Yn", $ncdModel->$dbField ?? '');
            }
            $this->setAttributesFromArray($element, [
                'pQid17_Abcde' => $ncdModel->dQid17_Abcde ?? '',
                'pQid18_Yn' => $ncdModel->dQid18_Yn ?? '',
                'pQid19_Yn' => $ncdModel->dQid19_Yn ?? '',
                'pQid19_Fbsmg' => $ncdModel->dQid19_Fbsmg ?? '',
                'pQid19_Fbsmmol' => $ncdModel->dQid19_Fbsmmol ?? '',
                'pQid19_Fbsdate' => $ncdModel->dQid19_Fbsdate ?? '',
                'pQid20_Yn' => $ncdModel->dQid20_Yn ?? '',
                'pQid20_Choleval' => $ncdModel->dQid20_Choleval ?? '',
                'pQid20_Choledate' => $ncdModel->dQid20_Choledate ?? '',
                'pQid21_Yn' => $ncdModel->dQid21_Yn ?? '',
                'pQid21_Ketonval' => $ncdModel->dQid21_Ketonval ?? '',
                'pQid21_Ketondate' => $ncdModel->dQid21_Ketondate ?? '',
                'pQid22_Yn' => $ncdModel->dQid22_Yn ?? '',
                'pQid22_Proteinval' => $ncdModel->dQid22_Proteinval ?? '',
                'pQid22_Proteindate' => $ncdModel->dQid22_Proteindate ?? '',
                'pQid23_Yn' => $ncdModel->dQid23_Yn ?? '',
                'pQid24_Yn' => $ncdModel->dQid24_Yn ?? '',
                'pReportStatus' => $ncdModel->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $ncdModel->dDeficiencyRemarks ?? '',
            ]);
        }
    }

    // SOAP
    private function buildSOAP(DOMDocument $doc, $soaps)
    {
        $soapWrapper = $doc->createElement('SOAPS');

        foreach ($soaps as $soapData) {
            $soapEntry = $doc->createElement('SOAP');
            $defaultAttributes = [
                'pHciCaseNo' => '',
                'pHciTransNo' => '',
                'pSoapDate' => '',
                'pPatientPin' => '',
                'pPatientType' => '',
                'pMemPin' => '',
                'pEffYear' => '',
                'pATC' => '',
                'pIsWalkedIn' => '',
                'pCoPay' => '',
                'pTransDate' => '',
                'pReportStatus' => '',
                'pDeficiencyRemarks' => ''
            ];
            $this->setAttributesFromArray($soapEntry, $defaultAttributes);

            $soapFields = [
                'pHciCaseNo' => $soapData->en_CaseNo ?? '', 'pHciTransNo' => $soapData->pHciTransNo ?? '',
                'pSoapDate' => $soapData->dSoapDate ?? '', 'pPatientPin' => $soapData->dPatientPin ?? '',
                'pPatientType' => $soapData->dPatientType ?? '', 'pMemPin' => $soapData->dMemPin ?? '',
                'pEffYear' => $soapData->dEffYear ?? '', 'pATC' => $soapData->dATC ?? '',
                'pIsWalkedIn' => $soapData->dIsWalkedIn ?? '', 'pCoPay' => $soapData->dCoPay ?? '',
                'pTransDate' => $soapData->dTransDate ?? '', 'pReportStatus' => $soapData->dReportStatus ?? 'U',
                'pDeficiencyRemarks' => $soapData->dDeficiencyRemarks ?? ''
            ];
            $this->setAttributesFromArray($soapEntry, $soapFields);
            $subj = $this->buildSubjective($doc, $soapData->subjective->first());
            $pep = $this->buildPepert($doc, $soapData->pepert->first());
            $pem = $this->buildSoapPeMisc($doc, $doc->createElement('PEMISC'), $soapData->peMisc);
            $pes = $this->buildSoapPespecific($doc, $soapData->peSpecific->first());
            $icd = $this->buildIcd($doc, $doc->createElement('ICDS'), $soapData->peMisc);
            $dia = $this->buildSoapDiagnostic($doc, $doc->createElement('DIAGNOSTICS'), $soapData->diagnostics);
            $man = $this->buildManagement($doc, $doc->createElement('MANAGEMENTS'), $soapData->managements);
            $adv = $this->buildAdvice($doc, $soapData->advice->first());

            $soapEntry->appendChild($subj);
            $soapEntry->appendChild($pep);
            $soapEntry->appendChild($pem);
            $soapEntry->appendChild($pes);
            $soapEntry->appendChild($icd);
            $soapEntry->appendChild($dia);
            $soapEntry->appendChild($man);
            $soapEntry->appendChild($adv);
            $soapWrapper->appendChild($soapEntry);
        }

        return $soapWrapper;
    }

    private function buildSubjective(DOMDocument $doc, $subj)
    {
        $subjWrapper = $doc->createElement('SUBJECTIVE');
        $subjFields = [
            'pIllnessHistory' => $subj->dIllnessHistory ?? '', 'pSignSymptoms' => $subj->dSignSymptoms ?? '',
            'pOtherComplaint' => $subj->dOtherComplaint ?? '', 'pPainSite' => $subj->dPainSite ?? '',
            'pReportStatus' => $subj->dReportStatus ?? 'U', 'pDeficiencyRemarks' => $subj->dDeficiencyRemarks ?? ''
        ];
        $this->setAttributesFromArray($subjWrapper, $subjFields);
        return $subjWrapper;
    }

    private function buildPepert(DOMDocument $doc, $pep)
    {
        $pepWrapper = $doc->createElement('PEPERT');
        $pepFields = [
            'pSystolic' => $pep->dSystolic ?? '',
            'pDiastolic' => $pep->dDiastolic ?? '',
            'pHr' => $pep->dHr ?? '',
            'pRr' => $pep->dRr ?? '',
            'pTemp' => $pep->dTemp ?? '',
            'pHeight' => $pep->dHeight ?? '',
            'pWeight' => $pep->dWeight ?? '',
            'pBMI' => $pep->dBmi ?? '',
            'pZScore' => $pep->dZscore ?? '',
            'pLeftVision' => $pep->dLeftVision ?? '',
            'pRightVision' => $pep->dRightVision ?? '',
            'pLength' => $pep->dLength ?? '',
            'pHeadCirc' => $pep->dHeadCirc ?? '',
            'pSkinfoldThickness' => $pep->dSkinfoldThickness ?? '',
            'pWaist' => $pep->dWaist ?? '',
            'pHip' => $pep->dHip ?? '',
            'pLimbs' => $pep->dLimbs ?? '',
            'pMidUpperArmCirc' => $pep->dMidUpperArmCirc ?? '',
            'pReportStatus' => $pep->dReportStatus ?? 'U',
            'pDeficiencyRemarks' => $pep->dDeficiencyRemarks ?? ''
        ];
        $this->setAttributesFromArray($pepWrapper, $pepFields);
        return $pepWrapper;
    }

    private function buildSoapPeMisc(DOMDocument $doc, DOMElement $wrapper, $peMisc)
    {
        if ($peMisc && $peMisc->count() > 0) {
            foreach ($peMisc as $misc) {
                $pemisc = $doc->createElement('PEMISC');
                $this->setAttributesFromArray($pemisc, [
                    'pSkinId' => $misc->dSkinId ?? '',
                    'pHeentId' => $misc->dHeentId ?? '',
                    'pChestId' => $misc->dChestId ?? '',
                    'pHeartId' => $misc->dHeartId ?? '',
                    'pAbdomenId' => $misc->dAbdomenId ?? '',
                    'pNeuroId' => $misc->dNeuroId ?? '',
                    'pRectalId' => $misc->dRectalId ?? '',
                    'pGuId' => $misc->dGuId ?? '',
                    'pReportStatus' => $misc->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $misc->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($pemisc);
            }
        }

        return $wrapper;
    }

    private function buildSoapPespecific(DOMDocument $doc, $pes)
    {
        $pesWrapper = $doc->createElement('PESPECIFIC');
        $pesFields = [
            'pSkinRem' => $pes->dSkinRem ?? '',
            'pHeentRem' => $pes->dHeentRem ?? '',
            'pChestRem' => $pes->dChestId ?? '',
            'pHeartRem' => $pes->dHeartRem ?? '',
            'pAbdomenRem' => $pes->dAbdomenRem ?? '',
            'pNeuroRem' => $pes->dNeuroRem ?? '',
            'pRectalId' => $pes->dRectalRem ?? '',
            'pGuRem' => $pes->dGuRem ?? '',
            'pReportStatus' => $pes->dReportStatus ?? 'U',
            'pDeficiencyRemarks' => $pes->dDeficiencyRemarks ?? ''
        ];
        $this->setAttributesFromArray($pesWrapper, $pesFields);
        return $pesWrapper;
    }

    private function buildIcd(DOMDocument $doc, DOMElement $wrapper, $icds)
    {
        if ($icds && $icds->count() > 0) {
            foreach ($icds as $icd) {
                $icdEl = $doc->createElement('PEMISC');
                $this->setAttributesFromArray($icdEl, [
                    'pIcdCode' => $icd->dIcdCode ?? '',
                    'pReportStatus' => $icd->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $icd->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($icdEl);
            }
        }

        return $wrapper;
    }

    private function buildSoapDiagnostic(DOMDocument $doc, DOMElement $wrapper, $diags)
    {
        if ($diags && $diags->count() > 0) {
            foreach ($diags as $diag) {
                $diagEl = $doc->createElement('DIAGNOSTIC');
                $this->setAttributesFromArray($diagEl, [
                    'pDiagnosticId' => $diag->dDiagnosticId ?? '',
                    'pOthRemarks' => $diag->dOthRemarks ?? '',
                    'pIsPhysicianRecommendation' => $diag->dIsPhysicianRecommend ?? '',
                    'pPatientRemarks' => $diag->dPatientRemarks ?? '',
                    'pReportStatus' => $diag->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $diag->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($diagEl);
            }
        }

        return $wrapper;
    }

    private function buildManagement(DOMDocument $doc, DOMElement $wrapper, $mans)
    {
        if ($mans && $mans->count() > 0) {
            foreach ($mans as $man) {
                $manEl = $doc->createElement('MANAGEMENT');
                $this->setAttributesFromArray($manEl, [
                    'pManagementId' => $diag->dManagementId ?? '',
                    'pOthRemarks' => $diag->dOthRemarks ?? '',
                    'pReportStatus' => $diag->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $diag->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($manEl);
            }
        }

        return $wrapper;
    }

    private function buildAdvice(DOMDocument $doc, $adv)
    {
        $advWrapper = $doc->createElement('ADVICE');
        $advFields = [
            'pRemarks' => $adv->dRemarks ?? '',
            'pReportStatus' => $adv->dReportStatus ?? 'U',
            'pDeficiencyRemarks' => $adv->dDeficiencyRemarks ?? ''
        ];
        $this->setAttributesFromArray($advWrapper, $advFields);
        return $advWrapper;
    }

    // DIAGNOSTICEXAMRESULT
    private function buildDiagnosticExamResult(DOMDocument $doc, $diags, $tranche)
    {
        $diagWrapper = $doc->createElement('DIAGNOSTICEXAMRESULTS');

        // Helper to initialize a container with default attributes
        $createContainer = function() use ($doc) {
            $container = $doc->createElement('DIAGNOSTICEXAMRESULT');
            $this->setAttributesFromArray($container, [
                'pHciCaseNo' => '', 'pHciTransNo' => '',
                'pPatientPin' => '', 'pPatientType' => '',
                'pMemPin' => '', 'pEffYear' => ''
            ]);
            return $container;
        };

        // --- CONTAINER 1: Glucose (FBS/RBS) ---
        $diagContainer1 = $createContainer();
        $fbs = $doc->createElement('FBSS');
        $rbs = $doc->createElement('RBSS');

        foreach ($diags as $diag) {
            // Update attributes based on the record
            $this->setAttributesFromArray($diagContainer1, [
                'pHciCaseNo' => $diag->en_CaseNo, 'pHciTransNo' => $diag->dTransNo,
                'pPatientPin' => $diag->dPatientPin, 'pPatientType' => $diag->dPatientType,
                'pMemPin' => $diag->dMemPin, 'pEffYear' => $diag->dEffyear
            ]);

            $this->buildFBS($doc, $fbs, $diag->fbs);
            $this->buildRBS($doc, $rbs, $diag->rbs);
        }

        $diagContainer1->appendChild($fbs);
        $diagContainer1->appendChild($rbs);
        $diagWrapper->appendChild($diagContainer1);

        // Only created if tranche is 'second'
        if ($tranche == 'second') {
            $diagContainer2 = $createContainer();
            $cbc = $doc->createElement('CBCS');
            $urinalysis = $doc->createElement('URINALYSIS');
            $cxr = $doc->createElement('CHESTXRAYS');
            $sput = $doc->createElement('SPUTUMS');
            $sput = $doc->createElement('SPUTUMS');
            $lipid = $doc->createElement('LIPIDPROFILES');
            $fbs = $doc->createElement('FBSS');
            $rbs = $doc->createElement('RBSS');
            $ecgs = $doc->createElement('ECGS');
            $fec = $doc->createElement('FECALYSISS');
            $pap = $doc->createElement('PAPSMEARS');
            $ogt = $doc->createElement('OGTTS');
            $cre = $doc->createElement('CREATININES');
            $ppd = $doc->createElement('PPDTests');
            $hba = $doc->createElement('HbA1cs');
            $oth = $doc->createElement('OTHERDIAGEXAMS');

            foreach ($diags as $diag) {
                $this->setAttributesFromArray($diagContainer2, [
                    'pHciCaseNo' => $diag->en_CaseNo, 'pHciTransNo' => $diag->dTransNo,
                    'pPatientPin' => $diag->dPatientPin, 'pPatientType' => $diag->dPatientType,
                    'pMemPin' => $diag->dMemPin, 'pEffYear' => $diag->dEffyear
                ]);

                $this->buildCBC($doc, $cbc, $diag->cbcs);
                $this->buildUrinalysis($doc, $urinalysis, $diag->urinalysis);
                $this->buildChestXray($doc, $cxr, $diag->cxrs);
                $this->buildSputum($doc, $sput, $diag->sputums);
                $this->buildLipidProfiles($doc, $lipid, $diag->lipidprofiles);
                $this->buildFBS($doc, $fbs, $diag->fbs);
                $this->buildRBS($doc, $rbs, $diag->rbs);
                $this->buildEcgs($doc, $ecgs, $diag->ecgs);
                $this->buildFecalysis($doc, $fec, $diag->fecalysis);
                $this->buildPapSmears($doc, $pap, $diag->papSmears);
                $this->buildOgtts($doc, $ogt, $diag->ogtts);
                $this->buildCreatinine($doc, $cre, $diag->creatinines);
                $this->buildPpdtests($doc, $ppd, $diag->ppdTests);
                $this->buildHbA1cs($doc, $hba, $diag->hba1cs);
                $this->buildOtherDiagExams($doc, $oth, $diag->otherDiagExams);
            }

            $diagContainer2->appendChild($cbc);
            $diagContainer2->appendChild($urinalysis);
            $diagContainer2->appendChild($cxr);
            $diagContainer2->appendChild($sput);
            $diagContainer2->appendChild($lipid);
            $diagContainer2->appendChild($fbs);
            $diagContainer2->appendChild($rbs);
            $diagContainer2->appendChild($ecgs);
            $diagContainer2->appendChild($fec);
            $diagContainer2->appendChild($pap);
            $diagContainer2->appendChild($ogt);
            $diagContainer2->appendChild($cre);
            $diagContainer2->appendChild($ppd);
            $diagContainer2->appendChild($hba);
            $diagContainer2->appendChild($oth);

            $diagWrapper->appendChild($diagContainer2);
        }

        return $diagWrapper;
    }

    private function buildRBS(DOMDocument $doc, DOMElement $wrapper, $rbss)
    {
        if ($rbss && $rbss->count() > 0) {
            foreach ($rbss as $rbs) {
                $rbsEl = $doc->createElement('RBS');
                $this->setAttributesFromArray($rbsEl, [
                    'pReferralFacility' => $rbs->dReferralFacility ?? '', 'pLabDate' => $rbs->dLabDate ?? '',
                    'pGlucoseMg' => $rbs->dGlucoseMg ?? '', 'pGlucoseMmol' => $rbs->dGlucoseMmol ?? '',
                    'pDateAdded' => $rbs->pDateAdded ?? '', 'pStatus' => $rbs->dStatus ?? '',
                    'pDiagnosticLabFee' => $rbs->dDiagnosticLabFee ?? '', 'pReportStatus' => $rbs->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $rbs->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($rbsEl);
            }

            return $wrapper;
        }
    }

    private function buildFBS(DOMDocument $doc, DOMElement $wrapper, $fbss)
    {
        if ($fbss && $fbss->count() > 0) {
            foreach ($fbss as $fbs) {
                $fbsEl = $doc->createElement('FBS');
                $this->setAttributesFromArray($fbsEl, [
                    'pReferralFacility' => $fbs->dReferralFacility ?? '', 'pLabDate' => $fbs->dLabDate ?? '',
                    'pGlucoseMg' => $fbs->dGlucoseMg ?? '', 'pGlucoseMmol' => $fbs->dGlucoseMmol ?? '',
                    'pDateAdded' => $fbs->pDateAdded ?? '', 'pStatus' => $fbs->dStatus ?? '',
                    'pDiagnosticLabFee' => $fbs->dDiagnosticLabFee ?? '', 'pReportStatus' => $fbs->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $fbs->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($fbsEl);
            }

            return $wrapper;
        }
    }

    private function buildCBC(DOMDocument $doc, DOMElement $wrapper, $cbcs)
    {
        if ($cbcs && $cbcs->count() > 0) {
            foreach ($cbcs as $cbc) {
                $cbcEl = $doc->createElement('CBC');
                $this->setAttributesFromArray($cbcEl, [
                    'pReferralFacility' => $cbc->dReferralFacility ?? '', 'pLabDate' => $cbc->dLabDate ?? '',
                    'pHemoglobin' => $cbc->dHemoglobin ?? '', 'pHematocrit' => $cbc->dHematocrit ?? '',
                    'pWBCCount' => $cbc->dWBCCount ?? '', 'pPlateletCount' => $cbc->dPlateletCount ?? '',
                    'pDateAdded' => $cbc->pDateAdded ?? '', 'pStatus' => $cbc->dStatus ?? '',
                    'pDiagnosticLabFee' => $cbc->dDiagnosticLabFee ?? '', 'pReportStatus' => $cbc->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $cbc->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($cbcEl);
            }

            return $wrapper;
        }
    }

    private function buildUrinalysis(DOMDocument $doc, DOMElement $wrapper, $urinalysis)
    {
        if ($urinalysis && $urinalysis->count() > 0) {
            foreach ($urinalysis as $ua) {
                $uaEl = $doc->createElement('URINALYSIS');
                $this->setAttributesFromArray($uaEl, [
                    'pReferralFacility' => $ua->dReferralFacility ?? '', 'pLabDate' => $ua->dLabDate ?? '',
                    'pColor' => $ua->dColor ?? '', 'pAppearance' => $ua->dAppearance ?? '',
                    'pPh' => $ua->dPh ?? '', 'pProtein' => $ua->dProtein ?? '',
                    'pGlucose' => $ua->dGlucose ?? '', 'pKetones' => $ua->dKetones ?? '',
                    'pBlood' => $ua->dBlood ?? '', 'pDateAdded' => $ua->pDateAdded ?? '',
                    'pStatus' => $ua->dStatus ?? '', 'pDiagnosticLabFee' => $ua->dDiagnosticLabFee ?? '',
                    'pReportStatus' => $ua->dReportStatus ?? 'U', 'pDeficiencyRemarks' => $ua->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($uaEl);
            }

            return $wrapper;
        }
    }

    private function buildChestXray(DOMDocument $doc, DOMElement $wrapper, $cxrs)
    {
        if ($cxrs && $cxrs->count() > 0) {
            foreach ($cxrs as $cxr) {
                $cxrEl = $doc->createElement('CHESTXRAY');
                $this->setAttributesFromArray($cxrEl, [
                    'pReferralFacility' => $cxr->dReferralFacility ?? '', 'pLabDate' => $cxr->dLabDate ?? '',
                    'pFindings' => $cxr->dFindings ?? '', 'pImpression' => $cxr->dImpression ?? '',
                    'pDateAdded' => $cxr->pDateAdded ?? '', 'pStatus' => $cxr->dStatus ?? '',
                    'pDiagnosticLabFee' => $cxr->dDiagnosticLabFee ?? '', 'pReportStatus' => $cxr->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $cxr->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($cxrEl);
            }

            return $wrapper;
        }
    }

    private function buildSputum(DOMDocument $doc, DOMElement $wrapper, $sputums)
    {
        if ($sputums && $sputums->count() > 0) {
            foreach ($sputums as $sputum) {
                $sputumEl = $doc->createElement('SPUTUM');
                $this->setAttributesFromArray($sputumEl, [
                    'pReferralFacility' => $sputum->dReferralFacility ?? '', 'pLabDate' => $sputum->dLabDate ?? '',
                    'pResult' => $sputum->dResult ?? '', 'pDateAdded' => $sputum->pDateAdded ?? '',
                    'pStatus' => $sputum->dStatus ?? '', 'pDiagnosticLabFee' => $sputum->dDiagnosticLabFee ?? '',
                    'pReportStatus' => $sputum->dReportStatus ?? 'U', 'pDeficiencyRemarks' => $sputum->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($sputumEl);
            }

            return $wrapper;
        }
    }

    private function buildLipidProfiles(DOMDocument $doc, DOMElement $wrapper, $lipids)
    {
        if ($lipids && $lipids->count() > 0) {
            foreach ($lipids as $lipid) {
                $lipidEl = $doc->createElement('LIPIDPROFILE');
                $this->setAttributesFromArray($lipidEl, [
                    'pReferralFacility' => $lipid->dReferralFacility ?? '', 'pLabDate' => $lipid->dLabDate ?? '',
                    'pCholesterol' => $lipid->dCholesterol ?? '', 'pTriglycerides' => $lipid->dTriglycerides ?? '',
                    'pHdl' => $lipid->dHdl ?? '', 'pLdl' => $lipid->dLdl ?? '',
                    'pDateAdded' => $lipid->pDateAdded ?? '', 'pStatus' => $lipid->dStatus ?? '',
                    'pDiagnosticLabFee' => $lipid->dDiagnosticLabFee ?? '', 'pReportStatus' => $lipid->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $lipid->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($lipidEl);
            }

            return $wrapper;
        }
    }

    private function buildEcgs(DOMDocument $doc, DOMElement $wrapper, $ecgs)
    {
        if ($ecgs && $ecgs->count() > 0) {
            foreach ($ecgs as $ecg) {
                $ecgEl = $doc->createElement('ECG');
                $this->setAttributesFromArray($ecgEl, [
                    'pReferralFacility' => $ecg->dReferralFacility ?? '', 'pLabDate' => $ecg->dLabDate ?? '',
                    'pFindings' => $ecg->dFindings ?? '', 'pImpression' => $ecg->dImpression ?? '',
                    'pDateAdded' => $ecg->pDateAdded ?? '', 'pStatus' => $ecg->dStatus ?? '',
                    'pDiagnosticLabFee' => $ecg->dDiagnosticLabFee ?? '', 'pReportStatus' => $ecg->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $ecg->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($ecgEl);
            }

            return $wrapper;
        }
    }

    private function buildFecalysis(DOMDocument $doc, DOMElement $wrapper, $fecalysis)
    {
        if ($fecalysis && $fecalysis->count() > 0) {
            foreach ($fecalysis as $fecal) {
                $fecalEl = $doc->createElement('FECALYSIS');
                $this->setAttributesFromArray($fecalEl, [
                    'pReferralFacility' => $fecal->dReferralFacility ?? '', 'pLabDate' => $fecal->dLabDate ?? '',
                    'pColor' => $fecal->dColor ?? '', 'pConsistency' => $fecal->dConsistency ?? '',
                    'pOccultBlood' => $fecal->dOccultBlood ?? '', 'pParasites' => $fecal->dParasites ?? '',
                    'pDateAdded' => $fecal->pDateAdded ?? '', 'pStatus' => $fecal->dStatus ?? '',
                    'pDiagnosticLabFee' => $fecal->dDiagnosticLabFee ?? '', 'pReportStatus' => $fecal->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $fecal->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($fecalEl);
            }

            return $wrapper;
        }
    }

    private function buildPapSmears(DOMDocument $doc, DOMElement $wrapper, $papSmears)
    {
        if ($papSmears && $papSmears->count() > 0) {
            foreach ($papSmears as $pap) {
                $papEl = $doc->createElement('PAPSMEAR');
                $this->setAttributesFromArray($papEl, [
                    'pReferralFacility' => $pap->dReferralFacility ?? '', 'pLabDate' => $pap->dLabDate ?? '',
                    'pFindings' => $pap->dFindings ?? '', 'pImpression' => $pap->dImpression ?? '',
                    'pDateAdded' => $pap->pDateAdded ?? '', 'pStatus' => $pap->dStatus ?? '',
                    'pDiagnosticLabFee' => $pap->dDiagnosticLabFee ?? '', 'pReportStatus' => $pap->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $pap->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($papEl);
            }

            return $wrapper;
        }
    }

    private function buildOgtts(DOMDocument $doc, DOMElement $wrapper, $ogtts)
    {
        if ($ogtts && $ogtts->count() > 0) {
            foreach ($ogtts as $ogtt) {
                $ogttEl = $doc->createElement('OGTT');
                $this->setAttributesFromArray($ogttEl, [
                    'pReferralFacility' => $ogtt->dReferralFacility ?? '', 'pLabDate' => $ogtt->dLabDate ?? '',
                    'pGlucoseMg' => $ogtt->dGlucoseMg ?? '', 'pGlucoseMmol' => $ogtt->dGlucoseMmol ?? '',
                    'pDateAdded' => $ogtt->pDateAdded ?? '', 'pStatus' => $ogtt->dStatus ?? '',
                    'pDiagnosticLabFee' => $ogtt->dDiagnosticLabFee ?? '', 'pReportStatus' => $ogtt->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $ogtt->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($ogttEl);
            }

            return $wrapper;
        }
    }

    private function buildFobts(DOMDocument $doc, DOMElement $wrapper, $fobts)
    {
        if ($fobts && $fobts->count() > 0) {
            foreach ($fobts as $fobt) {
                $fobtEl = $doc->createElement('FOBT');
                $this->setAttributesFromArray($fobtEl, [
                    'pReferralFacility' => $fobt->dReferralFacility ?? '', 'pLabDate' => $fobt->dLabDate ?? '',
                    'pResult' => $fobt->dResult ?? '', 'pDateAdded' => $fobt->pDateAdded ?? '',
                    'pStatus' => $fobt->dStatus ?? '', 'pDiagnosticLabFee' => $fobt->dDiagnosticLabFee ?? '',
                    'pReportStatus' => $fobt->dReportStatus ?? 'U', 'pDeficiencyRemarks' => $fobt->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($fobtEl);
            }

            return $wrapper;
        }
    }

    private function buildCreatinine(DOMDocument $doc, DOMElement $wrapper, $creatinines)
    {
        if ($creatinines && $creatinines->count() > 0) {
            foreach ($creatinines as $creatinine) {
                $creatinineEl = $doc->createElement('CREATININE');
                $this->setAttributesFromArray($creatinineEl, [
                    'pReferralFacility' => $creatinine->dReferralFacility ?? '', 'pLabDate' => $creatinine->dLabDate ?? '',
                    'pCreatinineMg' => $creatinine->dCreatinineMg ?? '', 'pCreatinineMmol' => $creatinine->dCreatinineMmol ?? '',
                    'pDateAdded' => $creatinine->pDateAdded ?? '', 'pStatus' => $creatinine->dStatus ?? '',
                    'pDiagnosticLabFee' => $creatinine->dDiagnosticLabFee ?? '', 'pReportStatus' => $creatinine->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $creatinine->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($creatinineEl);
            }

            return $wrapper;
        }
    }

    private function buildPpdtests(DOMDocument $doc, DOMElement $wrapper, $ppdTests)
    {
        if ($ppdTests && $ppdTests->count() > 0) {
            foreach ($ppdTests as $ppd) {
                $ppdEl = $doc->createElement('PPDTEST');
                $this->setAttributesFromArray($ppdEl, [
                    'pReferralFacility' => $ppd->dReferralFacility ?? '', 'pLabDate' => $ppd->dLabDate ?? '',
                    'pFindings' => $ppd->dFindings ?? '', 'pDateAdded' => $ppd->pDateAdded ?? '',
                    'pStatus' => $ppd->dStatus ?? '', 'pDiagnosticLabFee' => $ppd->dDiagnosticLabFee ?? '',
                    'pReportStatus' => $ppd->dReportStatus ?? 'U', 'pDeficiencyRemarks' => $ppd->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($ppdEl);
            }

            return $wrapper;
        }
    }

    private function buildHbA1cs(DOMDocument $doc, DOMElement $wrapper, $hbA1cs)
    {
        if ($hbA1cs && $hbA1cs->count() > 0) {
            foreach ($hbA1cs as $hbA1c) {
                $hbA1cEl = $doc->createElement('HBA1C');
                $this->setAttributesFromArray($hbA1cEl, [
                    'pReferralFacility' => $hbA1c->dReferralFacility ?? '', 'pLabDate' => $hbA1c->dLabDate ?? '', 'pFindings' => $hbA1c->dFindings ?? '',
                    'pDateAdded' => $hbA1c->pDateAdded ?? '', 'pStatus' => $hbA1c->dStatus ?? '',
                    'pDiagnosticLabFee' => $hbA1c->dDiagnosticLabFee ?? '', 'pReportStatus' => $hbA1c->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $hbA1c->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($hbA1cEl);
            }

            return $wrapper;
        }
    }

    private function buildOtherDiagExams(DOMDocument $doc, DOMElement $wrapper, $otherDiags)
    {
        if ($otherDiags && $otherDiags->count() > 0) {
            foreach ($otherDiags as $diag) {
                $diagEl = $doc->createElement('OTHERDIAGEXAM');
                $this->setAttributesFromArray($diagEl, [
                    'pReferralFacility' => $diag->dReferralFacility ?? '', 'pLabDate' => $diag->dLabDate ?? '',
                    'pOthDiagExam' => $diag->dOthDiagExam ?? '', 'pFindings' => $diag->dFindings ?? '',
                    'pDateAdded' => $diag->pDateAdded ?? '', 'pStatus' => $diag->dStatus ?? '',
                    'pDiagnosticLabFee' => $diag->dDiagnosticLabFee ?? '', 'pReportStatus' => $diag->dReportStatus ?? 'U',
                    'pDeficiencyRemarks' => $diag->dDeficiencyRemarks ?? '',
                ]);
                $wrapper->appendChild($diagEl);
            }

            return $wrapper;
        }
    }

    // MEDICINES
    private function buildMedicines(DOMDocument $doc, $meds)
    {
        $wrapper = $doc->createElement('MEDICINES');

        foreach ($meds as $med) {
            $container = $doc->createElement('MEDICINE');

            $values = [
                'pHciCaseNo' => $med->en_CaseNo, 'pHciTransNo' => $med->s_TransNo, 'pCategory' => $med->dCategory,
                'pDrugCode' => $med->dDrugCode, 'pGenericCode' => $med->dGenericCode, 'pSaltCode' => $med->dSaltCode,
                'pStrengthCode' => $med->dStrengthCode, 'pFormCode' => $med->dFormCode, 'pUnitCode' => $med->dUnitCode,
                'pPackageCode' => $med->dPackageCode, 'pOtherMedicine' => $med->dOtherMedicine, 'pOthMedDrugGrouping' => $med->dOtheMedDrugGrouping,
                'pRoute' => $med->dRoute, 'pQuantity' => $med->dQuantity, 'pActualUnitPrice' => $med->dActualUnitPrice,
                'pTotalAmtPrice' => $med->dTotalAmtPrice, 'pInstructionsQuantity' => $med->dInstructionsQuantity, 'pInstructionStrength' => $med->dInstructionsStrength,
                'pInstructionFrequency' => $med->dInstructionsFrequency, 'pPrescribingPhysician' => $med->dPrescribingPhysician, 'pIsDispensed' => $med->dIsDispensed,
                'pDateDispensed' => $med->dDateDispensed, 'pDispensingPersonnel' => $med->dDispensingPersonnel, 'pIsApplicable' => $med->dIsApplicable,
                'pDateAdded' => $med->dDateAdded, 'pReportStatus' => $med->dReportStatus, 'pDeficiencyRemarks' => $med->dDeficiencyRemarks
            ];

            $this->setAttributesFromArray($container, $values);
            $wrapper->appendChild($container);
        }

        return $wrapper;
    }

    private function setAttributesFromArray(DOMElement $element, array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $element->setAttribute($key, $value ?? '');
        }
    }
}
