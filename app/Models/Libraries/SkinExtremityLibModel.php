<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class SkinExtremityLibModel extends Model
{
    protected $table = 'dw_lib_skin_extremities';

    protected $fillable = [
        'skin_id',
        'skin_desc',
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
