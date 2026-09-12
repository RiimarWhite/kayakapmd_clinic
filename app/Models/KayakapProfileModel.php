<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KayakapProfileModel extends Model
{
    protected $table = 'kayakapmd_profile';

    protected $fillable = [
        'clientcode',
        'facility_type',
        'reports_address',
        'reports_citymunprov',
        'reports_contactnumber',
        'EMR_cert_number',
        'EMR_cert_issuance',
        'EMR_ID',
        'HOSP_NAME',
        'HOSP_ADDBRGY',
        'HOSP_ADDMUN',
        'HOSP_ADDPROV',
        'HOSP_ADDREG',
        'HOSP_ADDZIPCODE',
        'HOSP_ADDLHIO',
        'HOSP_CLASS',
        'ownership_type',
        'businessgroup_name',
        'SECTOR',
        'EMAIL_ADD',
        'TIN',
        'TEL_NO',
        'TELEFAX',
        'DATE_REGISTERED',
        'tokenreceived',
        'tokendatetime',
        'cipher_key',
        'enable_yakap',
        'enable_consultation',
        'enable_secretary',
        'enable_que',
        'enable_cashier',
        'enable_pharmacy',
        'enable_laboratory',
        'enable_radiology',
        'admin_name',
        'corp_secretary',
        'phic_incharge',
        'accountant'
    ];

    public $timestamps = false;

    public function casts() : array
    {
        return [
            'EMR_cert_issuance' => 'date',
            'DATE_REGISTERED' => 'date',
            'tokendatetime' => 'datetime',
            'enable_yakap' => 'boolean',
            'enable_consultation' => 'boolean',
            'enable_secretary' => 'boolean',
            'enable_que' => 'boolean',
            'enable_cashier' => 'boolean',
            'enable_pharmacy' => 'boolean',
            'enable_laboratory' => 'boolean',
            'enable_radiology' => 'boolean'
        ];
    }
}
