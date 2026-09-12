<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class ChestLibModel extends Model
{
    protected $table = 'dw_lib_chest';

    protected $fillable = [
        'chest_id',
        'chest_desc',
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
