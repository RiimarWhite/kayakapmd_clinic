<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class DigitalRectalLibModel extends Model
{
    protected $table = 'dw_lib_digital_rectal';

    protected $fillable = [
        'rectal_id',
        'rectal_desc',
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
