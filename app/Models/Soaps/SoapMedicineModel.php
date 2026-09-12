<?php

namespace App\Models\Soaps;

use App\Models\EnlistmentModel;
use App\Models\SOAPModel;
use Illuminate\Database\Eloquent\Model;

class SoapMedicineModel extends Model
{
    protected $table = 'dd_soap_medicine';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $fillable = [
        'dw_clientcode',
        'px_pin',
        'px_consultcode_cn',
        'en_CaseNo',
        's_TransNo',
        'dCategory',
        'dDrugCode',
        'dGenericCode',
        'dSaltCode',
        'dStrengthCode',
        'dFormCode',
        'dUnitCode',
        'dPackageCode',
        'dOtherMedicine',
        'dOthMedDrugGrouping',
        'dRoute',
        'dQuantity',
        'dActualUnitPrice',
        'dTotalAmtPrice',
        'dInstructionQuantity',
        'dInstructionStrength',
        'dInstructionFrequency',
        'dInstructionPhysician',
        'dIsDispensed',
        'dDateDispensed',
        'dDispensingPersonnel',
        'dIsApplicable',
        'dDateAdded',
        'dReportStatus',
        'dDeficiencyRemarks',
        'createdby',
        'created',
        'updatedby',
        'updated',
        'retail_price',
        'qty',
        'total_amt',
        'chargetype',
        'charge_refcode',
        'servicerefno',
        'clinic_cost',
        'clinic_srp',
        'pricetype',
    ];

    public function soap()
    {
        return $this->belongsTo(SOAPModel::class, 's_TransNo', 'pHciTransNo');
    }

    public function enlistment()
    {
        return $this->belongsTo(EnlistmentModel::class, 'en_CaseNo', 'dCaseNo');
    }
}
