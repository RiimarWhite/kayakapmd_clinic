<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class HeentLibModel extends Model
{
    protected $table = 'dw_lib_heent';

    protected $fillable = [
        'heent_id',
        'heent_desc',
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
