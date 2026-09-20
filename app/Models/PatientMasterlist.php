<?php

namespace App\Models;

use App\Http\Controllers\ConsultationController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PatientMasterlist extends Model
{
    protected $table = 'pxmasterlist';

    protected $fillable = [
        'pxrefno',
        'pxrecno',
        'en_transo',
        'pincode',
        'ipd_pincode',
        'ipc_casecode',
        'patientname',
        'pxlastname',
        'pxfirstname',
        'pxmidname',
        'pxsuffix',
        'gender',
        'birthday',
        'age',
        'religion',
        'nationality',
        'mobilenumber',
        'emailaddress',
        'address',
        'streetadrs',
        'brgy',
        'muncity',
        'province',
        'zipcode',
        'region',
        'country',
        'phic_pin',
        'ispwd',
        'senior_idno',
        'last_consultation',
        'last_enlistcode',
        'last_docrefno',
        'last_docname',
        'classification',
        'followupdate',
        'followupcheckup',
        'status',
        'recordedby',
        'recordeddate',
        'updatedby',
        'updated',
        'canaccess_online',
        'allow_emailnotification',
        'allow_sms'
    ];

    const CREATED_AT = 'recordeddate';
    const UPDATED_AT = 'updated';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->guard('secretary')->check()) {
                $user = auth()->guard('secretary')->user();
                $model->recordedby = $user->seclname . ', ' . $user->secfname . ' ' . $user->secmname . ' ' . $user->secsuffix;
            } elseif (auth()->guard('admin')->check()) {
                $user = auth()->guard('admin')->user();
                $model->recordedby = $user->adminname ?? $user->username ?? 'Admin';
            } elseif (auth()->guard('doctor')->check()) {
                $user = auth()->guard('doctor')->user();
                $model->recordedby = $user->docname ?? 'Doctor';
            }
        });

        static::updating(function ($model) {
            if (auth()->guard('secretary')->check()) {
                $user = auth()->guard('secretary')->user();
                $model->updatedby = $user->seclname . ', ' . $user->secfname . ' ' . $user->secmname . ' ' . $user->secsuffix;
            } elseif (auth()->guard('admin')->check()) {
                $user = auth()->guard('admin')->user();
                $model->updatedby = $user->adminname ?? $user->username ?? 'Admin';
            } elseif (auth()->guard('doctor')->check()) {
                $user = auth()->guard('doctor')->user();
                $model->updatedby = $user->docname ?? 'Doctor';
            }
        });
    }

    public function consultations() {
        return $this->hasMany(ConsultationModel::class, 'pxrefno', 'pxrefno');
    }
}
