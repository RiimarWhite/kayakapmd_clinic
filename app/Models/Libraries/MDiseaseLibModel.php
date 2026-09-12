<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class MDiseaseLibModel extends Model
{
    protected $table = 'dw_lib_mdiseases';

    protected $fillable = [
        'mdisease_code',
        'mdisease_desc',
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
