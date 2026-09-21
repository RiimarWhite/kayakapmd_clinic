<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Detailed Comment: PxChargesModel maps to the pxcharges table.
 * It manages individual patient consultation charges, retail items, service fees,
 * discounts, and payment methods for point-of-sale billing in the admin console.
 * Timestamps are disabled to match legacy schema with 'updated' column.
 */
class PxChargesModel extends Model
{
    protected $table = 'pxcharges';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'dw_clientcode',
        'transactiontype',
        'docrefno',
        'transdate',
        'docname',
        'phic_lib_id',
        'servicerefno',
        'servicename',
        'vatable',
        'retail',
        'quantity',
        'total',
        'discount',
        'net_total',
        'paymentrefno',
        'payment_type',
        'consultationrefno',
        'pxcode_pin',
        'pxname',
        'group_category_id',
        'group_category',
        'paymentmethod',
        'updatedby',
        'updated'
    ];
}
