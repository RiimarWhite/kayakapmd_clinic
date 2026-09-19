<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleModel extends Model
{
    protected $table = 'docschedules';

    protected $fillable = [
        'dw_clientcode',
        'schedrefno',
        'docrefno',
        'day',
        'start',
        'end',
        'notes'
    ];
}
