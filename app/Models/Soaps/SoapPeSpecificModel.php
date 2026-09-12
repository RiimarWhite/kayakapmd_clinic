<?php

namespace App\Models\Soaps;

use App\Models\EnlistmentModel;
use App\Models\SOAPModel;
use Illuminate\Database\Eloquent\Model;

class SoapPeSpecificModel extends Model
{
    protected $table = 'dd_soap_pespecific';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    protected $fillable = [
        'dw_clientcode',
        's_TransNo',
        'en_caseno',
        'dSkinRem',
        'dHeentRem',
        'dChestRem',
        'dHeartRem',
        'dAbdomenRem',
        'dNeuroRem',
        'dRectalRem',
        'dGuRem',
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
}
