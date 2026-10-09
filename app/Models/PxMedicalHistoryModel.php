<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Detailed Comment: PxMedicalHistoryModel maps to the pxmedicalhistory table.
 * It manages permanent patient clinical history records (allergies such as seafood,
 * injection/immunization log, past medical conditions, surgical history, family history,
 * maintenance medications, and special clinical warnings), which persist across all patient visits.
 */
class PxMedicalHistoryModel extends Model
{
    protected $table = 'pxmedicalhistory';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'pxrefno',
        'pincode',
        'allergies',
        'injections_immunization',
        'past_medical_history',
        'surgical_history',
        'family_history',
        'maintenance_medications',
        'notes',
        'recordedby',
        'updatedby',
    ];

    /**
     * Detailed Comment: Belongs to relation linking to the master patient record via pxrefno.
     */
    public function patient()
    {
        return $this->belongsTo(PatientMasterlist::class, 'pxrefno', 'pxrefno');
    }
}
