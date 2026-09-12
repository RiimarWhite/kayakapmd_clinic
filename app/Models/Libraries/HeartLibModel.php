<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class HeartLibModel extends Model
{
    protected $table = 'dw_lib_heart';

    protected $fillable = [
        'heart_id',
        'heart_desc',
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
