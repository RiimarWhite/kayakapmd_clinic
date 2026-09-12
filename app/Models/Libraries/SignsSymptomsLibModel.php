<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class SignsSymptomsLibModel extends Model
{
    protected $table = 'dw_lib_signs_symptoms';

    protected $fillable = [
        'symptoms_id',
        'symptoms_desc',
        'lib_stat',
        'updated',
        'updatedby',
        'sort_no',
        'date_deactivated',
        'deactivatedby',
        'sys_usertype',
        'dw_clientcode',
    ];
}
