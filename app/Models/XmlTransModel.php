<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XmlTransModel extends Model
{
    protected $table = 'xml_transmittal_reports';

    protected $primaryKey = 'UPLOAD_ID';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'dw_clientcode',
        'accre_no',
        'report_code',
        'trans_type',
        'tranche_type',
        'date_range_start',
        'date_range_end',
        'date_generated',
        'XML_CONTENT',
        'ENCRYPTED_CONTENT',
        'verifiedby',
        'status',
        'transmittal_date',
        'transmittal_refno',
        'transmittedby'
    ];
}
