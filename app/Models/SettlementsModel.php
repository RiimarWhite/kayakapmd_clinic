<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementsModel extends Model
{
    protected $table = 'pxsettlements';

    protected $fillable = [
        'transactionrefno',
        'consultationrefno',
        'net_total',
        'cash',
        'cta',
        'cta_type',
        'something',
        'hmo',
        'hmo_type'
    ];

    public function consultation() {
        return $this->hasOne(ConsultationModel::class, 'consultationrefno', 'consultationrefno');
    }
}
