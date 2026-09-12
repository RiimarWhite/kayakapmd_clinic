<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StocksLedger extends Model
{
    protected $table = 'stocks_ledger';
    public $timestamps = false;
    protected $fillable = [
        'dw_clientcode',
        'transactiontype',
        'px_pin',
        'px_consultcode_cn',
        'en_CaseNo',
        's_transno',
        'patient_name',
        'prodcode',
        'phic_reference_code',
        'item_dscr',
        'price_type',
        'yakap_essential',
        'yakap_essential_price',
        'hmocode',
        'hmoname',
        'pndf_enable',
        'phic_enable',
        'cost_ave',
        'retails',
        'qty',
        'unit',
        'vatamt',
        'totalamt',
        'item_grouping',
        'sub_grouping',
        'remarks',
        'updatedby',
        'updated',
        'dispenseby',
        'dispensed',
        'dispensed_status',
        'prescribed_quantity',
        'ins_strength',
        'ins_frequency',
    ];
}
