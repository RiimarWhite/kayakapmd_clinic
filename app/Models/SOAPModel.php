<?php

namespace App\Models;

use App\Models\Soaps\SoapAdviceModel;
use App\Models\Soaps\SoapDiagnosticModel;
use App\Models\Soaps\SoapIcdModel;
use App\Models\Soaps\SoapManagementModel;
use App\Models\Soaps\SoapMedicineModel;
use App\Models\Soaps\SoapPeMiscModel;
use App\Models\Soaps\SoapPepertModel;
use App\Models\Soaps\SoapPeSpecificModel;
use App\Models\Soaps\SoapSubjectiveModel;
use App\Models\PhicChargesModel;
use Illuminate\Database\Eloquent\Model;

class SOAPModel extends Model
{
    protected $table = 'dd_soap_1';

    protected $primaryKey = 'pHciTransNo';

    public $incrementing = false;   // if not auto-increment

    protected $keyType = 'string';  // ensure it preserves leading zeros / non-int formatting

    protected $fillable = [
        'dw_clientcode',
        'pHciTransNo',
        'px_pin',
        'px_consultcode_cn',
        'en_CaseNo',
        'visit_count_year',
        'yakap_applicable',
        'dSoapDate',
        'dPatientPin',
        'dPatientType',
        'dMemPin',
        'dEffYear',
        'dATC',
        'dIsWalkedIn',
        'dCoPay',
        'dTransDate',
        'dReportStatus',
        'dDeficiencyRemarks',
        'createdby',
        'created',
        'updatedby',
        'updated',
        'services_made',
        'total_payable',
        'cta_others',
        'cta_phic',
        'co_pay',
        'status',
        'report_code',
        'report_date',
        'report_code2',
        'report_date2'
    ];

    public $timestamps = false;

    public function patient()
    {
        return $this->belongsTo(PatientMasterlist::class, 'px_pin', 'pincode');
    }

    public function enlistment()
    {
        return $this->belongsTo(EnlistmentModel::class, 'en_CaseNo', 'dCaseNo');
    }

    public function advice()
    {
        return $this->hasMany(SoapAdviceModel::class, 's_TransNo', 'pHciTransNo');
    }

    public function diagnostics()
    {
        return $this->hasMany(SoapDiagnosticModel::class, 's_TransNo', 'pHciTransNo');
    }

    public function icd()
    {
        return $this->hasMany(SoapIcdModel::class, 's_TransNo', 'pHciTransNo');
    }

    public function management()
    {
        return $this->hasMany(SoapManagementModel::class, 's_TransNo', 'pHciTransNo');
    }

    public function medicines()
    {
        return $this->hasMany(SoapMedicineModel::class, 's_TransNo', 'pHciTransNo');
    }

    public function peMisc()
    {
        return $this->hasMany(SoapPeMiscModel::class, 's_TransNo', 'pHciTransNo');
    }

    public function pepert()
    {
        return $this->hasMany(SoapPepertModel::class, 's_TransNo', 'pHciTransNo');
    }

    public function peSpecific()
    {
        return $this->hasMany(SoapPeSpecificModel::class, 's_TransNo', 'pHciTransNo');
    }

    public function subjective()
    {
        return $this->hasMany(SoapSubjectiveModel::class, 's_TransNo', 'pHciTransNo');
    }

    public function phicCharges()
    {
        return $this->hasMany(PhicChargesModel::class, 's_TransNo', 'pHciTransNo');
    }
}
