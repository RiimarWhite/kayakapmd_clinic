<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HMOModel extends Model
{
    public $table = 'hmo_masterlist';

    public $fillable = [
        'dw_clientcode',
        'hmocode',
        'hmoname',
        'hmoaddress',
        'coacode',
        'accre_no',
        'hmotype'
    ];

    public $timestamps = false;
}
