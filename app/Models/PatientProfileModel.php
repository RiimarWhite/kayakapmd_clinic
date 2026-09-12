<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientProfileModel extends Model
{
    protected $table = 'dd_profile_1';

    protected $fillable = [
        'dw_clientcode',
        'dTransNo',
        'en_CaseNo'
    ];
}
