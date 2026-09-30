<?php

namespace App\Models\Stocks;

use Illuminate\Database\Eloquent\Model;

/**
 * Detailed Comment: StocksGroupingModel manages category-specific groupings for items and services,
 * supporting Imaging subgroups (xray, mri, ct scan, ultrasound, ob ultrasound, 2d echo),
 * Drugs & Meds subgroups, and customizable administration groups.
 */
class StocksGroupingModel extends Model
{
    protected $table = 'stocks_groupings';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'group_code',
        'category',
        'group_name',
        'description',
        'status',
        'created_by'
    ];
}
