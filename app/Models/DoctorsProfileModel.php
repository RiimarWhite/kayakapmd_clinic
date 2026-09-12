<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorsProfileModel extends Model
{
    protected $table = 'doctors';

    protected $fillable = [
        'doccode',
        'docrefno',
        'doclname',
        'docfname',
        'docmname',
        'suffix',
        'titlename',
        'docname',
        'adrs',
        'emailadd',
        'S2no',
        'PTR',
        'Licno',
        'phicno',
        'tin',
        'phicenable',
        'phicrate',
        'pfrate',
        'lastupdate',
        'proftype',
        'disabletext',
        'cellno',
        'catg',
        'recid',
        'redby',
        'station',
        'groupname',
        'coacode',
        'accountno',
        'tax',
        'issuehospOR',
        'expertise',
        'clinichours',
        'otherinfo',
        'biodata',
        'clinicroom',
        'quevisible',
        'profgroup',
        'autoAddVAT',
        'VAT',
        'allowtextresult',
        'allowdocsystem',
        'phicexpiry',
        'licnoexpiry',
        'status',
        'statusreason',
        'vatable',
        'rodrate',
        'phicname',
        'department',
        'docfirst'
    ];

    public $timestamps = false;

    public function casts() : array 
    {
        return [
            'phicenable' => 'boolean',
            'lastupdate' => 'datetime',
            'issuehospOP' => 'boolean',
            'quevisible' => 'boolean',
            'allowtextresult' => 'boolean',
            'allowdocsystem' => 'boolean',
            'phicexpiry' => 'date',
            'licnoexpiry' => 'date',
            'status' => 'boolean',
            'vatable' => 'boolean'
        ];
    }

    public static function booted()
    {
        static::creating(function ($model) {
            $model->doccode = 'PFMD' . str_pad($model->count() + 1, 3, '0', STR_PAD_LEFT);
        });
    }

    public function doctorrights()
    {
        return $this->hasOne(DoctorModel::class, 'docrefno', 'docrefno');
    }
}
