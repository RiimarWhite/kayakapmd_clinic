<?php

namespace App\Models\Stocks;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class StocksLedgerModel extends Model
{
    protected $table = 'stocks_ledger';

    protected $fillable = [
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
        'dispensed_status'
    ];

    CONST CREATED_AT = 'updated';
    CONST UPDATED_AT = 'updated';

    protected function casts() : array
    {
        return [
            'updated' => 'date',
            'dispensed' => 'date'
        ];
    }

    public static function booted()
    {
        static::updating(function ($model) {
            // Detailed Comment: Resolve updatedby defensively based on active authentication guard
            $guard = collect(['admin', 'doctor', 'secretary'])->first(fn($g) => auth()->guard($g)->check());
            $model->updatedby = match ($guard) {
                'admin' => auth()->user()?->username ?? 0,
                'doctor' => auth()->user()?->full_name ?? 0,
                'secretary' => auth()->user()?->full_name ?? 0,
                default => 0,
            };
        });
    }
}
