<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class GenitourinaryLibModel extends Model
{
    protected $table = 'dw_lib_genitourinary';

    protected $fillable = [
        'gu_id',
        'gu_desc',
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
