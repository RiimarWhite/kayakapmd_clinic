<?php

namespace App\Models;

use App\Models\Soaps\SoapMedicineModel;
use App\Models\Stocks\StocksLedgerModel;
use Illuminate\Database\Eloquent\Model;

class EnlistmentModel extends Model
{
    protected $table = 'dd_enlistment_1';

    protected $primaryKey = 'dTransNo';

    public $incrementing = false;   // if not auto-increment

    protected $keyType = 'string';  // ensure it preserves leading zeros / non-int formatting

    protected $fillable = [
        'dw_clientcode',
        'dCaseNo',
        'dTransNo',
        'dEffyear',
        'px_pin',
        'px_consultcode_cn',
        'dEnlistStat',
        'dEnlistDate',
        'dPackageType',
        'dMemPin',
        'dMemFname',
        'dMemMname',
        'dMemLname',
        'dMemExtname',
        'dMemDob',
        'dPatientPin',
        'dPatientFname',
        'dPatientMname',
        'dPatientLname',
        'dPatientExtname',
        'patientname',
        'dPatientSex',
        'dPatientDob',
        'dPatientType',
        'dPatientMobileNo',
        'dPatientLandlineNo',
        'dWithConsent',
        'dTransDate',
        'created',
        'dCreatedBy',
        'dReportStatus',
        'dDeficiencyRemarks',
        'updated',
        'updatedby',
        'for_payment',
        'with_LOA',
        'with_consent',
        'date_cancelled',
        'cancelledby',
        'date_transferred',
        'transferredby',
        'transferred_emr_provider_id',
        'transferred_facilitycode',
        'is_dependent_valid',
        'with_disability',
        'dependent_type',
        'avail_free_service',
        'XPS_MODULE',
        'report_trans_no',
        'cf4_claimid_no',
        'cf4_hci_transmittal_no',
        'px_mobileno',
        'px_landline',
        'px_emailadd',
    ];

    public $timestamps = false;

    CONST CREATED_AT = 'created';
    CONST UPDATED_AT = 'updated';

    public function soaps()
    {
        return $this->hasMany(SOAPModel::class, 'en_CaseNo', 'dCaseNo');
    }

    public function profiles()
    {
        return $this->hasMany(ProfileModel::class, 'en_CaseNo', 'dCaseNo');
    }

    public function diagExamResults()
    {
        return $this->hasMany(DiagExamResult::class, 'en_CaseNo', 'dCaseNo');
    }

    public function meds()
    {
        return $this->hasMany(SoapMedicineModel::class, 'px_consultcode_cn', 'px_consultcode_cn');
    }
}
