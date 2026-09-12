<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class ImmElderlyLibModel extends Model
{
    protected $table = 'dw_lib_immelderly';

    protected $fillable = [
        'immcode',
        'imm_desc',
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
