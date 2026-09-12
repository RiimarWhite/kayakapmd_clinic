<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineMasterlistModel extends Model
{
    protected $table = 'medicine_masterlist';

    protected $fillable = [
        'medicine_refno',
        'medicine_name',
        'philhealth_refno'
    ];
}
