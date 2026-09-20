<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationModel extends Model
{
    protected $table = "pxwalkinconsultation";

    /**
     * Detailed Comment: consultationrefno serves as the unique string primary key for pxwalkinconsultation
     */
    protected $primaryKey = 'consultationrefno';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Detailed Comment: Default model attributes to satisfy database integrity constraints (finadiagnosis NOT NULL)
     */
    protected $attributes = [
        'finadiagnosis' => '',
    ];

    /**
     * Detailed Comment: Fillable attributes for pxwalkinconsultation matching database data dictionary.
     * Includes queueno, secrefno, doccode, and caseno for queue and consultation persistence.
     */
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
        'queueno',
        'secrefno',
        'laboratorypath',
        'radiologypath',
        'photo_path',
        'hmocode',
        'hmoname',
        'instructions',
        'foradmit',
        'foradmit_instructions'
    ];

    public $timestamps = false;

    /**
     * Detailed Comment: Model lifecycle hooks to initialize reference codes and track authoring user
     * across secretary, doctor, and admin authentication guards.
     */
    public static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->consultationrefno)) {
                $model->consultationrefno = 'CON' . now()->format('mdYHis');
            }

            if (auth()->guard('secretary')->check()) {
                $user = auth()->guard('secretary')->user();
                $model->source_data = $model->source_data ?: 'SECRETARY';
                $name = trim(($user->seclname ?? '') . ', ' . ($user->secfname ?? '') . ' ' . ($user->secmname ?? '') . ' ' .  ($user->secsuffix ?? ''));
                $model->recordedby = $name ?: ($user->username ?? 'SECRETARY');
            } else if (auth()->guard('doctor')->check()) {
                $user = auth()->guard('doctor')->user();
                $model->source_data = $model->source_data ?: 'DOCTOR';
                $name = trim(($user->doclname ?? '') . ', ' . ($user->docfname ?? '') . ' ' . ($user->docmname ?? '') . ' ' .  ($user->suffix ?? ''));
                $model->recordedby = $name ?: ($user->username ?? 'DOCTOR');
            } else if (auth()->guard('admin')->check()) {
                $user = auth()->guard('admin')->user();
                // Detailed Comment: Set source_data to 'ADMIN' matching updated ENUM definition on pxwalkinconsultation
                $model->source_data = $model->source_data ?: 'ADMIN';
                $model->recordedby = $user->username ?? 'admin';
            } else {
                $model->source_data = $model->source_data ?: 'SECRETARY';
                $model->recordedby = $model->recordedby ?: 'system';
            }

            $model->recordeddate = now();
        });

        static::updating(function ($model) {
            if (auth()->guard('secretary')->check()) {
                $user = auth()->guard('secretary')->user();
                $name = trim(($user->seclname ?? '') . ', ' . ($user->secfname ?? '') . ' ' . ($user->secmname ?? '') . ' ' .  ($user->secsuffix ?? ''));
                $model->recordedby = $name ?: ($user->username ?? 'SECRETARY');
            } else if (auth()->guard('doctor')->check()) {
                $user = auth()->guard('doctor')->user();
                $name = trim(($user->doclname ?? '') . ', ' . ($user->docfname ?? '') . ' ' . ($user->docmname ?? '') . ' ' .  ($user->suffix ?? ''));
                $model->recordedby = $name ?: ($user->username ?? 'DOCTOR');
            } else if (auth()->guard('admin')->check()) {
                $user = auth()->guard('admin')->user();
                $model->recordedby = $user->username ?? 'admin';
            }

            $model->recordeddate = now();
        });
    }

    public function patient()
    {
        return $this->belongsTo(PatientMasterlist::class, 'pxrefno', 'pxrefno');
    }
}
