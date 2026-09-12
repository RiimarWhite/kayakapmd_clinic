<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagExamResult extends Model
{
    protected $table = 'dd_diag_1_examresults';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $fillable = [
        'dw_clientcode',
        'en_CaseNo',
        's_TransNo',
        'dPatientPin',
        'dPatientType',
        'dMemPin',
        'dEffYear',
        'createdby',
        'created',
        'updatedby',
        'updated',
    ];
}
