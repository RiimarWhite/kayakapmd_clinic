<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class NCDQLibModel extends Model
{
    protected $table = 'dw_lib_ncdq';

    protected $fillable = [
        'QID',
        'HID',
        'QUESTION_DESC',
        'PARENT_QID',
        'updated',
        'updatedBy',
        'sys_usertype',
        'dw_clientcode'
    ];
}
