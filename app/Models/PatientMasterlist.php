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
        'casecode',
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
        'last_consultation',
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
            if (auth()->guard('secretary')->user()) {
                $user = auth()->guard('secretary')->user();

                $model->recordedby = $user->seclname . ', ' . $user->secfname . ' ' . $user->secmname . ' ' . $user->secsuffix;
            }
        });

        static::updating(function ($model) {
            if (auth()->guard('secretary')->user()) {
                $user = auth()->guard('secretary')->user();

                $model->updatedby = $user->seclname . ', ' . $user->secfname . ' ' . $user->secmname . ' ' . $user->secsuffix;
            }
        });
    }

    public function consultations() {
        return $this->hasMany(ConsultationModel::class, 'pxrefno', 'pxrefno');
    }
}
