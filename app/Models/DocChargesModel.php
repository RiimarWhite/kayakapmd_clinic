<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocChargesModel extends Model
{
    protected $table = 'doctorcharges';

    protected $fillable = [
        'pxchargerefno',
        'docrefno',
        'transdate',
        'docname',
        'servicerefno', // aka charges
        'servicename',
        'quantity',
        'total',
        'discount',
        'net_total',
        'paymentrefno',
        'consultationrefno',
        'charge',
        'paymentmethod'
    ];

    public $timestamps = false;
}
