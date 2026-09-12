<?php

namespace App\Models\DiagExamResults;

use App\Models\EnlistmentModel;
use Illuminate\Database\Eloquent\Model;

class DiagCbcModel extends Model
{
    protected $table = 'dd_diag_cbc';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $fillable = [
        'dw_clientcode',
        'px_pin',
        'px_consultcode_cn',
        'en_CaseNo',
        's_TransNo',
        'dReferralFacility',
        'dLabDate',
        'dHematocrit',
        'dHemoglobinG',
        'dHemoglobinMmol',
        'dMhcPg',
        'dMhcFmol',
        'dMchGhb',
        'dMchcMmol',
        'dMcvUm',
        'dMcvFl',
        'dWbc1000',
        'dWbc10',
        'dMyelocyte',
        'dNeutrophilsBnd',
        'dNeutrophilsSeg',
        'dLympocytes',
        'dMonocytes',
        'dEosinophilis',
        'dBasophilis',
        'dPlatelet',
        'dDateAdded',
        'dStatus',
        'dDiagnosticLabFee',
        'dReportStatus',
        'dDeficiencyRemarks',
        'createdby',
        'created',
        'updatedby',
        'updated',
        'chargetype',
        'charge_refcode',
        'servicerefno',
        'clinic_cost',
        'clinic_srp',
        'pricetype',
        'co_pay',
    ];

    public function enlistment()
    {
        return $this->belongsTo(EnlistmentModel::class, 'en_CaseNo', 'dCaseNo');
    }
}
