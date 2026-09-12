<?php

namespace App\Models;

use App\Models\Libraries\MedicineLibModel;
use Illuminate\Database\Eloquent\Model;

class PhicChargesModel extends Model
{
    protected $table = 'phic_charges';
    public $timestamps = false;
    protected $fillable = [
        'dw_clientcode',
        'px_pin',
        'px_consultcode_cn',
        'en_caseno',
        's_TransNo',
        'pxname',
        'item_grouping',
        'prod_code',
        'drug_code',
        'gen_code',
        'salt_code',
        'strength_code',
        'form_code',
        'unit_code',
        'package_code',
        'route',
        'generic_name',
        'prescribed_quantity',
        'ins_strength',
        'ins_frequency',
        'qty',
        'unit',
        'actual_price',
        'co_payment',
        'total_price',
        'doc_code',
        'doc_name',
        'is_applicable',
        'checkup_trans_no',
        'status',
        'deficiency_remarks',
        'updated',
        'updatedby',
        'is_dispensed',
        'dispensed_date',
        'dispensedby',
        'category',
        'sub_grouping'
    ];

    public function medicineDetails()
    {
        return $this->belongsTo(MedicineLibModel::class, 'drug_code', 'DRUG_CODE');
    }

    public function getMedicineAttribute()
    {
        return $this->medicineDetails->DRUG_DESC ?? $this->generic_name ?? $this->drug_code;
    }
}
