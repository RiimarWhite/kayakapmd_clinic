<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XmlEnlistUploading extends Model
{
    protected $table = 'xml_enlist_uploading';

    protected $primaryKey = 'UPLOAD_ID';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'dw_clientcode',
        'UPLOAD_ID',
        'UPLOAD_XML',
        'DATE_UPLOADED',
        'RANGE_DATE',
        'status',
        'importedby',
        'imported'
    ];
}
