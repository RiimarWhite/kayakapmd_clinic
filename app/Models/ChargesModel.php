<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChargesModel extends Model
{
    protected $table = 'charges_masterlist';

    protected $fillable = [
        'chargerefno',
        'charge_name',
        'charge_category',
        'charge_amount',
    ];

    public $timestamps = false;
}
