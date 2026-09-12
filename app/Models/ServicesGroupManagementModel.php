<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicesGroupManagementModel extends Model
{
    protected $table = 'servicesgroups';

    protected $fillable = [
        'servicegroup_refno',
        'servicegroup_name',
        'servicegroup_dscr'
    ];

    public $timestamps = false;
}
