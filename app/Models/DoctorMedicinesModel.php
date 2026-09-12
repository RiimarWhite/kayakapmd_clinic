<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorMedicinesModel extends Model
{
    protected $table = "pxrxdocuments";

    protected $fillable = [
        'rxreferenceno',
        'medicinequantity',
        'consultationrefno',
        'medicinecode',
        'medicinedosage',
        'medicinename',
        'medicineduration',
        'createddate',
        'createdby',
        'docrefno',
        'sentdate',
        'sentby',
        'templatename'
    ];

    public $timestamps = false;
}
