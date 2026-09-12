<?php

namespace App\Models\DiagExamResults;

use App\Models\EnlistmentModel;
use Illuminate\Database\Eloquent\Model;

class DiagUrinalysisModel extends Model
{
    protected $table = 'dd_diag_urinalysis';

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
        'dGravity',
        'dAppearance',
        'dColor',
        'dGlucose',
        'dProteins',
        'dKetones',
        'dPh',
        'dRbCells',
        'dWbCells',
        'dBacteria',
        'dCrystals',
        'dBladderCell',
        'dSquamousCell',
        'dTubularCell',
        'dBroadCasts',
        'dEpithelialCast',
        'dGranularCast',
        'dHyalineCast',
        'dRbcCast',
        'dWaxyCast',
        'dWcCast',
        'dAlbumin',
        'dPusCells',
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
