<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class AbdomenLibModel extends Model
{
    protected $table = 'dw_lib_abdomen';

    protected $fillable = [
        'abdomen_id',
        'abdomen_desc',
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
