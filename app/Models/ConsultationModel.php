<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationModel extends Model
{
    protected $table = "pxwalkinconsultation";

    protected $fillable = [
        'source_data',
        'consultationrefno',
        'pxrefno',
        'pincode',
        'caseno',
        'en_transno',
        'en_caseno',
        'soap_caseno',
        'patientname',
        'pxlastname',
        'pxfirstname',
        'pxmidname',
        'pxsuffix',
        'phic_pin',
        'secretary_note',
        'infectious_risk_type',
        'docrefno',
        'docname',
        'doctors_infectious_risk',
        'gender',
        'birthday',
        'age',
        'doccoaopd',
        'mobilenumber',
        'emailaddress',
        'classification',
        'subclassification',
        'weight',
        'wunit',
        'height',
        'hunit',
        'temp',
        'tempunit',
        'requesteddate',
        'requestedby',
        'consulted',
        'consulteddate',
        'paymentrefno',
        'reasonforconsultation',
        'impression',
        'finadiagnosis',
        'pe_evaluation',
        'followup',
        'verifiedpaymentdate',
        'verifiedby',
        'followupdate',
        'respiratoryrate',
        'pulserate',
        'bpnumerator',
        'bpdenominator',
        'consultation_date',
        'status',
        'laboratorypath',
        'radiologypath',
        'photo_path',
        'hmocode',
        'hmoname'
    ];

    public $timestamps = false;

    public static function booted()
    {
        static::creating(function ($model) {
            $model->consultationrefno = 'CON' . now()->format('mdYHis');

            if (auth()->guard('secretary')->check()) {
                $user = auth()->guard('secretary')->user();
                $model->source_data = 'SECRETARY';
                $model->recordedby = trim($user->seclname . ' ' . $user->secfname . ' ' . $user->secmname . ' ' .  $user->secsuffix);
            } else if (auth()->guard('doctor')->check()) {
                $user = auth()->guard('doctor')->user();
                $model->source_data = 'DOCTOR';
                $model->recordedby = trim($user->doclname . ' ' . $user->docfname . ' ' . $user->docmname . ' ' .  $user->suffix);
            }

            $model->recordeddate = now();
        });

        static::updating(function ($model) {
            if (auth()->guard('secretary')->check()) {
                $user = auth()->guard('secretary')->user();
                $model->source_data = 'SECRETARY';
                $model->recordedby = trim($user->seclname . ' ' . $user->secfname . ' ' . $user->secmname . ' ' .  $user->secsuffix);
            } else if (auth()->guard('doctor')->check()) {
                $user = auth()->guard('doctor')->user();
                $model->source_data = 'DOCTOR';
                $model->recordedby = trim($user->doclname . ' ' . $user->docfname . ' ' . $user->docmname . ' ' .  $user->suffix);
            }

            $model->recordeddate = now();
        });
    }

    public function patient()
    {
        return $this->belongsTo(PatientMasterlist::class, 'pxrefno', 'pxrefno');
    }
}
