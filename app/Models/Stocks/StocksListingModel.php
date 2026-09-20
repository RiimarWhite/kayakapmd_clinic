<?php

namespace App\Models\Stocks;

use Illuminate\Database\Eloquent\Model;

class StocksListingModel extends Model
{
    protected $table = 'stocks_listing';

    protected $fillable = [
        'prodcode',
        'item_grouping',
        'phic_reference_code',
        'drug_generic',
        'drug_brand',
        'drug_dosage',
        'drug_preperation',
        'drug_add_dscr',
        'drug_grouping',
        'drug_type',
        'drug_prescription_type',
        'unit',
        'prod_itemdscr',
        'oecb_code',
        'oecb_price',
        'yakap_essential',
        'yakap_essential_code',
        'yakap_essential_price',
        'pndf_enable',
        'phic_enable',
        'last_delivery_no',
        'last_delivery_date',
        'last_delivery_cost',
        'cost_ave',
        'price_regular',
        'price_phic',
        'price_hmo',
        'price_others',
        'is_inventory',
        'qty',
        'qty_level_reorder',
        'po_code_lastdelivery',
        'supplierid',
        'suppliername',
        'remarks'
    ];

    CONST CREATED_AT = 'updated';
    CONST UPDATED_AT = 'updated';

    protected function casts(): array
    {
        return [
            'updated' => 'date'
        ];
    }

    public static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->prodcode)) {
                $model->prodcode = 'PROD' . now()->format('mdYHis');
            }

            // Detailed Comment: Resolve updatedby defensively based on active authentication guard
            $guard = collect(['admin', 'doctor', 'secretary'])->first(fn($g) => auth()->guard($g)->check());
            $model->updatedby = match ($guard) {
                'admin' => auth()->user()?->username ?? 0,
                'doctor' => auth()->user()?->full_name ?? 0,
                'secretary' => auth()->user()?->full_name ?? 0,
                default => 0,
            };
        });

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
