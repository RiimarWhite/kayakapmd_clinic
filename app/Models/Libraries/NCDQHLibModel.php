<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class NCDQHLibModel extends Model
{
    protected $table = 'dw_lib_ncdqh';

    protected $fillable = [
        'HID',
        'HEADER_DESC',
        'sys_usertype',
        'dw_clientcode'
    ];
}
