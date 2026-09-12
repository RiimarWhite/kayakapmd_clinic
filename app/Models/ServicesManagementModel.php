<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicesManagementModel extends Model
{
    protected $table = 'doctorservices';

    protected $fillable = [
        'servicerefno',
        'servicename',
        'servicedscr',
        'servicecharge',
        'category',
        'docrefno'
    ];

    public $timestamps = false;
}
