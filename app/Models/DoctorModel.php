<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;

class DoctorModel extends Authenticatable
{
    protected $table = 'doctorsrights';

    protected $fillable = [
        'docrefno',
        'doclname',
        'docfname',
        'docmname',
        'suffix',
        'titlename',
        'username',
        'pass',
        'eadd',
        'mnumber',
        'tin',
        'address',
        'slcode',
        'taxpercent',
        'bankacct',
        'updateID',
        'updated',
        'updatedby',
        'Adminsys',
        'mobileapp',
        'logged',
        'status',
        'expertise',
        'proftype',
        'doctype',
        'docmgmt',
        'logged_in',
        'consultationfee'
    ];

    protected $hidden = [
        'pass'
    ];

    public $timestamps = false;

    protected function casts() : array 
    {
        return [
            'pass' => 'hashed',
            'updated' => 'date',
            'logged_in' => 'boolean'
        ];
    }

    public static function booted()
    {   
        static::creating(function ($model) {
            $model->docrefno = now()->format('mdYHis') . 'MD';
        });
    }

    protected function fullName()
    {
        return Attribute::make(
            get: fn () => "{$this->doclname}, {$this->docfname} {$this->docmname}"
        );
    }
}
