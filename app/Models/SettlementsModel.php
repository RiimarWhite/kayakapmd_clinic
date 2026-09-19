<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Detailed Comment: SettlementsModel maps to the pxsettlements table.
 * It manages billing settlement records across Cash, Card (CTA), and HMO channels.
 * Timestamps are disabled since pxsettlements uses a legacy 'created' datetime column.
 * consultationrefno serves as the unique string primary key.
 * Virtual attributes (net_total, cash, cta, hmo) are appended to preserve frontend contract compatibility.
 */
class SettlementsModel extends Model
{
    /**
     * Detailed Comment: Disable automatic Eloquent timestamps (created_at / updated_at)
     * as pxsettlements schema does not have them.
     */
    public $timestamps = false;

    protected $table = 'pxsettlements';

    protected $primaryKey = 'consultationrefno';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'transactionrefno',
        'consultationrefno',
        'pincode',
        'docrefno',
        'docname',
        'total_doctorspf',
        'total_vaccines',
        'total_immunizations',
        'total_meds',
        'total_lab',
        'total_xray',
        'total_procedures',
        'total_supplies',
        'total_others',
        'total_gross',
        'less_vat',
        'less_srpwd',
        'less_hmo',
        'less_phic',
        'less_govt',
        'less_discount',
        'net_payable',
        'payment_cash',
        'payment_card',
        'cta_type',
        'payment_wallet',
        'payment_pn',
        'hmocode',
        'hmoname',
        'hmo_type',
        'createdby',
        'created',
        'cashierbatch',
        'cashier_date',
        'cashiername',
        'journalcode',
        'slcode',
        // Virtual alias fillables mapped via mutators to actual DB columns
        'net_total',
        'cash',
        'cta',
        'hmo',
    ];

    /**
     * Detailed Comment: Automatically append virtual aliases so that JSON serialized models
     * contain net_total, cash, cta, and hmo required by frontend views and API tests.
     */
    protected $appends = [
        'net_total',
        'cash',
        'cta',
        'hmo',
    ];

    /**
     * Detailed Comment: Mutator & Accessor for net_total.
     * Maps to both total_gross and net_payable in pxsettlements.
     */
    public function getNetTotalAttribute()
    {
        return isset($this->attributes['net_payable'])
            ? number_format((float) $this->attributes['net_payable'], 2, '.', '')
            : (isset($this->attributes['total_gross']) ? number_format((float) $this->attributes['total_gross'], 2, '.', '') : '0.00');
    }

    public function setNetTotalAttribute($value)
    {
        $val = (float) $value;
        $this->attributes['total_gross'] = $val;
        $this->attributes['net_payable'] = $val;
    }

    /**
     * Detailed Comment: Mutator & Accessor for cash.
     * Maps to payment_cash in pxsettlements.
     */
    public function getCashAttribute()
    {
        return isset($this->attributes['payment_cash'])
            ? number_format((float) $this->attributes['payment_cash'], 2, '.', '')
            : '0.00';
    }

    public function setCashAttribute($value)
    {
        $this->attributes['payment_cash'] = (float) $value;
    }

    /**
     * Detailed Comment: Mutator & Accessor for cta.
     * Maps to payment_card in pxsettlements.
     */
    public function getCtaAttribute()
    {
        return isset($this->attributes['payment_card'])
            ? number_format((float) $this->attributes['payment_card'], 2, '.', '')
            : '0.00';
    }

    public function setCtaAttribute($value)
    {
        $this->attributes['payment_card'] = (float) $value;
    }

    /**
     * Detailed Comment: Mutator & Accessor for hmo.
     * Maps to less_hmo in pxsettlements.
     */
    public function getHmoAttribute()
    {
        return isset($this->attributes['less_hmo'])
            ? number_format((float) $this->attributes['less_hmo'], 2, '.', '')
            : '0.00';
    }

    public function setHmoAttribute($value)
    {
        $this->attributes['less_hmo'] = (float) $value;
    }

    public function consultation()
    {
        return $this->hasOne(ConsultationModel::class, 'consultationrefno', 'consultationrefno');
    }
}
