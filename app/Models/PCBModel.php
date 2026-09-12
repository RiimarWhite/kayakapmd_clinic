<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PCBModel extends Model
{
    protected $table = 'pcb';
    protected $primaryKey = 'clientcode';

    protected $fillable = [
        'userid',
        'passwd',
        'hciaccreno',
        'pmccno',
        'enlistTotalcnt',
        'profileTotalcnt',
        'certificationid',
        'hcitransmittalnumber',
        'facilityName',
        'region',
        'updatedby',
        'updated'
    ];

    public $incrementing = false;
    public $timestamps = false;

    CONST UPDATED_AT = 'updated';
}
