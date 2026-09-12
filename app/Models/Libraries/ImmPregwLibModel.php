<?php

namespace App\Models\Libraries;

use Illuminate\Database\Eloquent\Model;

class ImmPregwLibModel extends Model
{
    protected $table = 'dw_lib_immpregw';

    protected $fillable = [
        'imm_code',
        'imm_desc',
        'lib_stat',
        'updated',
        'updatedby',
        'sort_no',
        'date_deactivated',
        'deactivatedby',
    ];
}
