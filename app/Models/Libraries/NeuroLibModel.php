<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class NeuroLibModel extends Model
{
    protected $table = 'dw_lib_neuro';

    protected $fillable = [
        'neuro_id',
        'neuro_desc',
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
