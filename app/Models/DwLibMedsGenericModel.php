<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * DwLibMedsGenericModel
 * 
 * Detailed Comment: Represents the PhilHealth/clinic generic medicines reference library (`dw_lib_meds_generic`),
 * providing generic drug codes (`gen_code`) and generic drug descriptions (`gen_desc`) for drug cataloging and auto-naming.
 */
class DwLibMedsGenericModel extends Model
{
    protected $table = 'dw_lib_meds_generic';
    protected $primaryKey = 'gen_code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'gen_code',
        'gen_desc',
        'updated',
        'updatedby'
    ];
}
