<?php

namespace App\Models;

use App\Models\Profiles\ProfileBloodTypeModel;
use App\Models\Profiles\ProfileFamHistModel;
use App\Models\Profiles\ProfileFhSpecificModel;
use App\Models\Profiles\ProfileImmunizationModel;
use App\Models\Profiles\ProfileMedHistModel;
use App\Models\Profiles\ProfileMensHistModel;
use App\Models\Profiles\ProfileMhSpecificModel;
use App\Models\Profiles\ProfileNcdQansModel;
use App\Models\Profiles\ProfilePeGenSurveyModel;
use App\Models\Profiles\ProfilePeMiscModel;
use App\Models\Profiles\ProfilePepertModel;
use App\Models\Profiles\ProfilePeSpecificModel;
use App\Models\Profiles\ProfilePregHistModel;
use App\Models\Profiles\ProfileSocHistModel;
use App\Models\Profiles\ProfileSurgHistModel;
use Illuminate\Database\Eloquent\Model;

class ProfileModel extends Model
{
    protected $table = 'dd_profile_1';

    protected $primaryKey = 'dTransNo';

    public $incrementing = false;   // if not auto-increment

    protected $keyType = 'string';  // ensure it preserves leading zeros / non-int formatting

    public $timestamps = false;    // if not using created_at and updated_at

    protected $fillable = [
        'dw_clientcode',
        'dTransNo',
        'en_CaseNo',
        'px_consultcode_cn',
        'dProfDate',
        'dPatientPin',
        'patientname',
        'dPatientType',
        'dPatientAge',
        'dMemPin',
        'dEffyear',
        'dATC',
        'dIsWalkedIn',
        'dTransDate',
        'dReportStatus',
        'dDeficiencyRemarks',
        'updated',
        'updatedby',
        'date_cancelled',
        'date_transferred',
        'cancelledby',
        'transferredby',
        'date_for_payment',
        'for_paymentby',
        'remarks',
        'presc_type',
        'profile_otp',
        'report_trans_no',
        'is_finalize',
        'xps_module',
        'with_atc',
    ];

    public function enlistment()
    {
        return $this->belongsTo(EnlistmentModel::class, 'en_CaseNo', 'dCaseNo');
    }

    public function bloodTypes()
    {
        return $this->hasMany(ProfileBloodTypeModel::class, 'p_TransNo', 'dTransNo');
    }

    public function famHist()
    {
        return $this->hasMany(ProfileFamHistModel::class, 'p_TransNo', 'dTransNo');
    }

    public function fhSpecific()
    {
        return $this->hasMany(ProfileFhSpecificModel::class, 'p_TransNo', 'dTransNo');
    }

    public function immunizations()
    {
        return $this->hasMany(ProfileImmunizationModel::class, 'p_TransNo', 'dTransNo');
    }

    public function medHist()
    {
        return $this->hasMany(ProfileMedHistModel::class, 'p_TransNo', 'dTransNo');
    }

    public function mensHist()
    {
        return $this->hasMany(ProfileMensHistModel::class, 'p_TransNo', 'dTransNo');
    }

    public function mhSpecific()
    {
        return $this->hasMany(ProfileMhSpecificModel::class, 'p_TransNo', 'dTransNo');
    }

    public function ncdQans()
    {
        return $this->hasMany(ProfileNcdQansModel::class, 'p_TransNo', 'dTransNo');
    }

    public function peGenSurvey()
    {
        return $this->hasMany(ProfilePeGenSurveyModel::class, 'p_TransNo', 'dTransNo');
    }

    public function peMisc()
    {
        return $this->hasMany(ProfilePeMiscModel::class, 'p_TransNo', 'dTransNo');
    }

    public function pepert()
    {
        return $this->hasMany(ProfilePepertModel::class, 'p_TransNo', 'dTransNo');
    }

    public function peSpecific()
    {
        return $this->hasMany(ProfilePeSpecificModel::class, 'p_TransNo', 'dTransNo');
    }

    public function pregHist()
    {
        return $this->hasMany(ProfilePregHistModel::class, 'p_TransNo', 'dTransNo');
    }

    public function socHist()
    {
        return $this->hasMany(ProfileSocHistModel::class, 'p_TransNo', 'dTransNo');
    }

    public function surgHist()
    {
        return $this->hasMany(ProfileSurgHistModel::class, 'p_TransNo', 'dTransNo');
    }
}
