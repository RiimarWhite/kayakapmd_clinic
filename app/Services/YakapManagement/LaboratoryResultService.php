<?php

namespace App\Services\YakapManagement;

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
use App\Models\EnlistmentModel;
use App\Services\ClientService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Maps laboratory tab form fields (diagnostic_{id}_*) to dd_diag_* tables.
 * Suffix keys match blade name attributes after the diagnostic_{id}_ prefix.
 */
class LaboratoryResultService
{
    /** @var array<string, class-string<Model>> */
    public const DIAGNOSTIC_MODEL_MAP = [
        '1' => DiagCbcModel::class,
        '2' => DiagUrinalysisModel::class,
        '3' => DiagFecalysisModel::class,
        '4' => DiagChestXrayModel::class,
        '5' => DiagSputumModel::class,
        '6' => DiagLipidProfileModel::class,
        '7' => DiagFbsModel::class,
        '8' => DiagCreatineModel::class,
        '9' => DiagEcgModel::class,
        '13' => DiagPapSmearModel::class,
        '14' => DiagOgttModel::class,
        '15' => DiagFobtModel::class,
        '17' => DiagPpdTestModel::class,
        '18' => DiagHba1cModel::class,
        '19' => DiagRbsModel::class,
        '99' => DiagOtherDiagExamModel::class,
    ];

    private $clientCode;

    public function __construct(
        private ClientService $clientService
    ) {
        $this->clientCode = $this->clientService->getClientCode();
    }

    public function save(
        string $caseNo,
        string $soapTransNo,
        string $pxPin,
        array $laboratoryResults,
        // array $planManagement,
        EnlistmentModel $enlistment,
    ): void {

        foreach (array_keys(self::DIAGNOSTIC_MODEL_MAP) as $id) {
            $modelClass = self::DIAGNOSTIC_MODEL_MAP[$id];

            $block = $this->extractBlock($laboratoryResults, $id);

            if (empty($block)) {
                continue;
            }

            $row = $this->buildRowForDiagnostic($id, $block, $caseNo, $soapTransNo, $pxPin);
            Log::info("block");
            Log::info($block);
            Log::info("row");
            Log::info($row);
            if ($row === null) {
                continue;
            }

            $modelClass::updateOrCreate(
                ['s_TransNo' => $soapTransNo],
                $row
            );
        }

        $this->upsertMasterExamResult($caseNo, $soapTransNo, $enlistment);
        // dd(true);
    }

    public function saveSingle(
        string $diagnosticId,
        string $caseNo,
        string $soapTransNo,
        string $pxPin,
        array $laboratoryResults,
        EnlistmentModel $enlistment,
    ): void {
        if (! isset(self::DIAGNOSTIC_MODEL_MAP[$diagnosticId])) {
            return;
        }

        $modelClass = self::DIAGNOSTIC_MODEL_MAP[$diagnosticId];
        $block = $this->extractBlock($laboratoryResults, $diagnosticId);
        $row = $this->buildRowForDiagnostic($diagnosticId, $block, $caseNo, $soapTransNo, $pxPin);

        if ($row === null) {
            return;
        }

        $modelClass::updateOrCreate(['s_TransNo' => $soapTransNo], $row);
        $this->upsertMasterExamResult($caseNo, $soapTransNo, $enlistment);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function loadByTransNo(string $soapTransNo): array
    {
        $out = [];
        foreach (self::DIAGNOSTIC_MODEL_MAP as $id => $modelClass) {
            /** @var Model|null $row */
            $row = $modelClass::query()->where('s_TransNo', $soapTransNo)->first();
            if ($row === null) {
                continue;
            }
            $attrs = $row->getAttributes();
            unset($attrs['id']);
            $suffixMap = $this->rowToSuffixMap((string) $id, $attrs);
            if ($suffixMap !== []) {
                $flat = [];
                foreach ($suffixMap as $suffix => $value) {
                    $flat['diagnostic_' . $id . '_' . $suffix] = $value;
                }
                $out[(string) $id] = $flat;
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $laboratoryResults
     * @return array<string, string>
     */
    private function extractBlock(array $laboratoryResults, string $id): array
    {
        $prefix = 'diagnostic_' . $id . '_';
        $out = [];
        foreach ($laboratoryResults as $k => $v) {
            if (! is_string($k) || ! str_starts_with($k, $prefix)) {
                continue;
            }
            $suffix = substr($k, strlen($prefix));
            $out[$suffix] = is_scalar($v) || $v === null ? (string) $v : '';
        }

        return $out;
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>|null
     */
    private function buildRowForDiagnostic(
        string $id,
        array $b,
        string $caseNo,
        string $soapTransNo,
        string $pxPin,
    ): ?array {
        $base = $this->baseColumns($caseNo, $soapTransNo, $pxPin);
        $now = CarbonImmutable::now()->format('Y-m-d');

        return match ($id) {
            '1' => array_merge($base, $this->buildCbc($b, $now)),
            '2' => array_merge($base, $this->buildUrinalysis($b, $now)),
            '3' => array_merge($base, $this->buildFecalysis($b, $now)),
            '4' => array_merge($base, $this->buildChestXray($b, $now)),
            '5' => array_merge($base, $this->buildSputum($b, $now)),
            '6' => array_merge($base, $this->buildLipidProfile($b, $now)),
            '7' => array_merge($base, $this->buildFbs($b, $now)),
            '8' => array_merge($base, $this->buildCreatinine($b, $now)),
            '9' => array_merge($base, $this->buildEcg($b, $now)),
            '13' => array_merge($base, $this->buildPapSmear($b, $now)),
            '14' => array_merge($base, $this->buildOgtt($b, $now)),
            '15' => array_merge($base, $this->buildFobt($b, $now)),
            '17' => array_merge($base, $this->buildPpd($b, $now)),
            '18' => array_merge($base, $this->buildHba1c($b, $now)),
            '19' => array_merge($base, $this->buildRbs($b, $now)),
            '99' => array_merge($base, $this->buildOther($b, $now)),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function baseColumns(string $caseNo, string $soapTransNo, string $pxPin): array
    {
        return [
            'dw_clientcode' => $this->clientCode,
            'px_pin' => $pxPin,
            'px_consultcode_cn' => '',
            'en_CaseNo' => $caseNo,
            's_TransNo' => $soapTransNo,
        ];
    }

    private function upsertMasterExamResult(string $caseNo, string $soapTransNo, EnlistmentModel $enlistment): void
    {
        DiagExamResult::query()->updateOrCreate(
            [
                'en_CaseNo' => $caseNo,
                's_TransNo' => $soapTransNo,
            ],
            [
                'dw_clientcode' => $this->clientCode,
                'dPatientPin' => $enlistment->dPatientPin ?? '',
                'dPatientType' => $enlistment->dPatientType ?? 'MM',
                'dMemPin' => $enlistment->dMemPin ?? '',
                'dEffYear' => $enlistment->dEffyear ?? '',
            ]
        );
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildCbc(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dHematocrit' => $b['hematocrit'] ?? '',
            'dHemoglobinG' => $b['hemoglobin_gdL'] ?? '',
            'dHemoglobinMmol' => $b['hemoglobin_mmolL'] ?? '',
            'dMhcPg' => $b['mhc_pgcell'] ?? '',
            'dMhcFmol' => $b['mhc_fmolcell'] ?? '',
            'dMchGhb' => $b['mchc_gHbdL'] ?? '',
            'dMchcMmol' => $b['mchc_mmolHbL'] ?? '',
            'dMcvUm' => $b['mcv_um'] ?? '',
            'dMcvFl' => $b['mcv_fL'] ?? '',
            'dWbc1000' => $b['wbc_cellsmmuL'] ?? '',
            'dWbc10' => $b['wbc_cellsL'] ?? '',
            'dMyelocyte' => $b['myelocyte'] ?? '',
            'dNeutrophilsBnd' => $b['neutrophils_bands'] ?? '',
            'dNeutrophilsSeg' => $b['neutrophils_segmenters'] ?? '',
            'dLympocytes' => $b['lymphocytes'] ?? '',
            'dMonocytes' => $b['monocytes'] ?? '',
            'dEosinophilis' => $b['eosinophils'] ?? '',
            'dBasophilis' => $b['basophils'] ?? '',
            'dPlatelet' => $b['platelet'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildUrinalysis(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dGravity' => $b['sg'] ?? '',
            'dAppearance' => $b['appearance'] ?? '',
            'dColor' => $b['color'] ?? '',
            'dGlucose' => $b['glucose'] ?? '',
            'dProteins' => $b['proteins'] ?? '',
            'dKetones' => $b['ketones'] ?? '',
            'dPh' => $b['pH'] ?? '',
            'dRbCells' => $b['rbc'] ?? '',
            'dWbCells' => $b['wbc'] ?? '',
            'dBacteria' => $b['bacteria'] ?? '',
            'dCrystals' => $b['crystals'] ?? '',
            'dBladderCell' => $b['bladder_cells'] ?? '',
            'dSquamousCell' => $b['squamous_cells'] ?? '',
            'dTubularCell' => $b['tubular_cells'] ?? '',
            'dBroadCasts' => $b['broad_casts'] ?? '',
            'dEpithelialCast' => $b['epithelial_cell_casts'] ?? '',
            'dGranularCast' => $b['granular_casts'] ?? '',
            'dHyalineCast' => $b['hyaline_casts'] ?? '',
            'dRbcCast' => $b['rbc_casts'] ?? '',
            'dWaxyCast' => $b['waxy_casts'] ?? '',
            'dWcCast' => $b['wc_casts'] ?? '',
            'dAlbumin' => $b['alb'] ?? '',
            'dPusCells' => $b['pus'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildFecalysis(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dColor' => $b['color'] ?? '',
            'dConsistency' => $b['consistency'] ?? '',
            'dRbc' => $b['rbc'] ?? '',
            'dWbc' => $b['wbc'] ?? '',
            'dOva' => $b['ova'] ?? '',
            'dParasite' => $b['parasite'] ?? '',
            'dBlood' => $b['blood'] ?? '',
            'dPusCells' => $b['pus'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildChestXray(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dObservation' => $b['chest_observe'] ?? '',
            'dRemarksObservation' => $b['chest_observe_remarks'] ?? '',
            'dFindings' => $b['chest_findings'] ?? '',
            'dRemarksFindings' => $b['chest_findings_remarks'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildSputum(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dDataCollection' => 'X',
            'dFindings' => $b['sputum'] ?? null,
            'dRemarks' => $b['sputum_remarks'] ?? '',
            'dNoPlusses' => $b['plusses'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildLipidProfile(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dLdl' => $b['ldl'] ?? '',
            'dHdl' => $b['hdl'] ?? '',
            'dTotal' => $b['cholesterol'] ?? '',
            'dCholesterol' => $b['cholesterol'] ?? '',
            'dTriglycerides' => $b['triglycerides'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildFbs(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dGlucoseMg' => $b['glucose_mgdL'] ?? '',
            'dGlucoseMmol' => $b['glucose_mmolL'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildCreatinine(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dFindings' => $b['creatinine_mgdl'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildEcg(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dFindings' => $b['ecg'] ?? null,
            'dRemarks' => $b['ecg_remarks'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildPapSmear(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dFindings' => $b['papsSmearFindings'] ?? '',
            'dImpression' => $b['papsSmearImpression'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildOgtt(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dExamFastingMg' => $b['fasting_mg'] ?? '',
            'dExamFastingMmol' => $b['fasting_mmol'] ?? '',
            'dExamOgttOneHrMg' => $b['oneHr_mg'] ?? '',
            'dExamOgttOneHrMmol' => $b['oneHr_mmol'] ?? '',
            'dExamOgttTwoHrMg' => $b['twoHr_mg'] ?? '',
            'dExamOgttTwoHrMmol' => $b['twoHr_mmol'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildFobt(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dFindings' => $b['fobt'] ?? null,
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildPpd(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dFindings' => $b['ppdt'] ?? null,
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildHba1c(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dFindings' => $b['hba1c_mmol'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildRbs(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dGlucoseMg' => $b['glucose_mgdL'] ?? '',
            'dGlucoseMmol' => $b['glucose_mmolL'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     * @return array<string, mixed>
     */
    private function buildOther(array $b, string $now): array
    {
        return [
            'dReferralFacility' => $this->referralFacility($b),
            'dLabDate' => $this->parseLabDate($b['lab_exam_date'] ?? ''),
            'dOthDiagExam' => '',
            'dFindings' => $b['oth1'] ?? '',
            'dDateAdded' => $now,
            'dStatus' => $b['status'] ?? 'N',
            'dDiagnosticLabFee' => $b['lab_fee'] ?? '',
            'dReportStatus' => 'U',
            'dDeficiencyRemarks' => '',
        ];
    }

    /**
     * @param  array<string, string>  $b
     */
    private function referralFacility(array $b): string
    {
        $labExam = $b['lab_exam'] ?? '1';
        $fac = trim((string) ($b['accre_diag_fac'] ?? ''));

        return $labExam === '0' ? $fac : '';
    }

    private function parseLabDate(string $value): string
    {
        $t = trim($value);
        if ($t === '') {
            return '1901-01-01';
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $t)) {
            return $t;
        }
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $t, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[1], (int) $m[2]);
        }

        try {
            return CarbonImmutable::parse($t)->format('Y-m-d');
        } catch (\Throwable) {
            return '1901-01-01';
        }
    }

    private function parseMoney(string $value): float
    {
        $v = preg_replace('/[^\d.\-]/', '', $value);

        return $v === '' || $v === '-' ? 0.0 : (float) $v;
    }

    /**
     * Inverse of builders: DB column => form suffix for a diagnostic id.
     *
     * @param  array<string, mixed>  $attrs
     * @return array<string, string>
     */
    private function rowToSuffixMap(string $id, array $attrs): array
    {
        $col = function (string $dbKey) use ($attrs): string {
            $v = $attrs[$dbKey] ?? '';

            return is_scalar($v) || $v === null ? (string) $v : '';
        };

        $common = [
            'status' => $col('dStatus'),
            'lab_exam' => ($col('dReferralFacility') !== '') ? '0' : '1',
            'accre_diag_fac' => $col('dReferralFacility'),
            'lab_exam_date' => $this->formatDateForForm($col('dLabDate')),
            'lab_fee' => $col('dDiagnosticLabFee'),
        ];

        return match ($id) {
            '1' => array_merge($common, [
                'hematocrit' => $col('dHematocrit'),
                'hemoglobin_gdL' => $col('dHemoglobinG'),
                'hemoglobin_mmolL' => $col('dHemoglobinMmol'),
                'mhc_pgcell' => $col('dMhcPg'),
                'mhc_fmolcell' => $col('dMhcFmol'),
                'mchc_gHbdL' => $col('dMchGhb'),
                'mchc_mmolHbL' => $col('dMchcMmol'),
                'mcv_um' => $col('dMcvUm'),
                'mcv_fL' => $col('dMcvFl'),
                'wbc_cellsmmuL' => $col('dWbc1000'),
                'wbc_cellsL' => $col('dWbc10'),
                'myelocyte' => $col('dMyelocyte'),
                'neutrophils_bands' => $col('dNeutrophilsBnd'),
                'neutrophils_segmenters' => $col('dNeutrophilsSeg'),
                'lymphocytes' => $col('dLympocytes'),
                'monocytes' => $col('dMonocytes'),
                'eosinophils' => $col('dEosinophilis'),
                'basophils' => $col('dBasophilis'),
                'platelet' => $col('dPlatelet'),
            ]),
            '2' => array_merge($common, [
                'sg' => $col('dGravity'),
                'crystals' => $col('dCrystals'),
                'appearance' => $col('dAppearance'),
                'bladder_cells' => $col('dBladderCell'),
                'color' => $col('dColor'),
                'squamous_cells' => $col('dSquamousCell'),
                'glucose' => $col('dGlucose'),
                'tubular_cells' => $col('dTubularCell'),
                'proteins' => $col('dProteins'),
                'broad_casts' => $col('dBroadCasts'),
                'ketones' => $col('dKetones'),
                'epithelial_cell_casts' => $col('dEpithelialCast'),
                'pH' => $col('dPh'),
                'granular_casts' => $col('dGranularCast'),
                'pus' => $col('dPusCells'),
                'hyaline_casts' => $col('dHyalineCast'),
                'alb' => $col('dAlbumin'),
                'rbc_casts' => $col('dRbcCast'),
                'rbc' => $col('dRbCells'),
                'waxy_casts' => $col('dWaxyCast'),
                'wbc' => $col('dWbCells'),
                'wc_casts' => $col('dWcCast'),
                'bacteria' => $col('dBacteria'),
            ]),
            '3' => array_merge($common, [
                'color' => $col('dColor'),
                'consistency' => $col('dConsistency'),
                'pus' => $col('dPusCells'),
                'rbc' => $col('dRbc'),
                'wbc' => $col('dWbc'),
                'ova' => $col('dOva'),
                'parasite' => $col('dParasite'),
                'blood' => $col('dBlood'),
            ]),
            '4' => array_merge($common, [
                'chest_observe' => $col('dObservation'),
                'chest_observe_remarks' => $col('dRemarksObservation'),
                'chest_findings' => $col('dFindings'),
                'chest_findings_remarks' => $col('dRemarksFindings'),
            ]),
            '5' => array_merge($common, [
                'sputum' => $col('dFindings'),
                'sputum_remarks' => $col('dRemarks'),
                'plusses' => $col('dNoPlusses'),
            ]),
            '6' => array_merge($common, [
                'ldl' => $col('dLdl'),
                'hdl' => $col('dHdl'),
                'cholesterol' => $col('dCholesterol'),
                'triglycerides' => $col('dTriglycerides'),
            ]),
            '7' => array_merge($common, [
                'glucose_mgdL' => $col('dGlucoseMg'),
                'glucose_mmolL' => $col('dGlucoseMmol'),
            ]),
            '8' => array_merge($common, [
                'creatinine_mgdl' => $col('dFindings'),
            ]),
            '9' => array_merge($common, [
                'ecg' => $col('dFindings'),
                'ecg_remarks' => $col('dRemarks'),
            ]),
            '13' => array_merge($common, [
                'papsSmearFindings' => $col('dFindings'),
                'papsSmearImpression' => $col('dImpression'),
            ]),
            '14' => array_merge($common, [
                'fasting_mg' => $col('dExamFastingMg'),
                'fasting_mmol' => $col('dExamFastingMmol'),
                'oneHr_mg' => $col('dExamOgttOneHrMg'),
                'oneHr_mmol' => $col('dExamOgttOneHrMmol'),
                'twoHr_mg' => $col('dExamOgttTwoHrMg'),
                'twoHr_mmol' => $col('dExamOgttTwoHrMmol'),
            ]),
            '15' => array_merge($common, [
                'fobt' => $col('dFindings'),
            ]),
            '17' => array_merge($common, [
                'ppdt' => $col('dFindings'),
            ]),
            '18' => array_merge($common, [
                'hba1c_mmol' => $col('dFindings'),
            ]),
            '19' => array_merge($common, [
                'glucose_mgdL' => $col('dGlucoseMg'),
                'glucose_mmolL' => $col('dGlucoseMmol'),
            ]),
            '99' => array_merge($common, [
                'oth1' => $col('dFindings'),
            ]),
            default => $common,
        };
    }

    private function formatDateForForm(string $ymd): string
    {
        $ymd = trim($ymd);
        if ($ymd === '' || $ymd === '1901-01-01') {
            return '';
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $ymd, $m)) {
            return sprintf('%02d/%02d/%04d', (int) $m[2], (int) $m[3], (int) $m[1]);
        }

        return $ymd;
    }
}
