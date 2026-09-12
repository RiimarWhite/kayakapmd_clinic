<?php

namespace App\Models\DiagExamResults;

use App\Models\EnlistmentModel;
use Illuminate\Database\Eloquent\Model;

class DiagOtherDiagExamModel extends Model
{
    protected $table = 'dd_diag_otherdiagexam';

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
        'dOthDiagExam',
        'dFindings',
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
