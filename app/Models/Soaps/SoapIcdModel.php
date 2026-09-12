<?php

namespace App\Models\Soaps;

use App\Models\EnlistmentModel;
use App\Models\SOAPModel;
use Illuminate\Database\Eloquent\Model;

class SoapIcdModel extends Model
{
    protected $table = 'dd_soap_icd';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $fillable = [
        'dw_clientcode',
        'px_pin',
        'px_consultcode_cn',
        's_TransNo',
        'en_caseno',
        'dIcdCode',
        'dReportStatus',
        'dDeficiencyRemarks',
        'seq_no',
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
}
