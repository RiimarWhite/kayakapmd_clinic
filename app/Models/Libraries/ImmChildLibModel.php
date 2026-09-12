<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class ImmChildLibModel extends Model
{
    protected $table = 'dw_lib_immchild';

    protected $fillable = [
        'imm_code',
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
