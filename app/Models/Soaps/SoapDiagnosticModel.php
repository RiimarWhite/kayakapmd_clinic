<?php

namespace App\Models\Soaps;

use App\Models\EnlistmentModel;
use App\Models\Libraries\DiagnosticsLibModel;
use App\Models\SOAPModel;
use Illuminate\Database\Eloquent\Model;

class SoapDiagnosticModel extends Model
{
    protected $table = 'dd_soap_diagnostic';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $fillable = [
        'dw_clientcode',
        'px_pin',
        'px_consultcode_cn',
        's_TransNo',
        'en_caseno',
        'dDiagnosticId',
        'dOthRemarks',
        'dIsPhysicianRecommend',
        'dPatientRemarks',
        'dReportStatus',
        'dDeficiencyRemarks',
        'createdby',
        'created',
        'updatedby',
        'updated',
    ];

    public function soap()
    {
        return $this->belongsTo(SOAPModel::class, 's_TransNo', 'pHciTransNo');
    }

    public function enlistment()
    {
        return $this->belongsTo(EnlistmentModel::class, 'en_caseno', 'dCaseNo');
    }

    public function diagnosticDescription()
    {
        return $this->belongsTo(DiagnosticsLibModel::class, 'dDiagnosticId', 'diagnostic_id');
    }
}
