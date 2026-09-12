<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecretaryDoctorsModel extends Model
{
    protected $table = 'secretary_doctor';

    protected $fillable = [
        'docrefno',
        'secrefno',
        'recordedby',
        'recordeddate',
        'active'
    ];

    public $timestamps = false;
}
