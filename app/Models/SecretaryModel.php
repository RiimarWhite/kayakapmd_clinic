<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;

class SecretaryModel extends Authenticatable
{
    protected $table = 'secretaryrights';

    protected $fillable = [
        'secrefno',
        'secidno',
        'secpassword',
        'secfname',
        'secmname',
        'seclname',
        'secsuffix',
        'secbday',
        'secgender',
        'secadrs',
        'seccontactno',
        'secemail',
        'recordedby',
        'recordeddate',
        'logged',
        'verifieddate',
        'verified'
    ];

    protected $hidden = [
        'secpassword'
    ];

    public $timestamps = false;

    protected function casts() : array
    {
        return [
            'secpassword' => 'hashed',
            'logged' => 'boolean',
            'verified' => 'boolean'
        ];
    }

    public static function booted()
    {
        static::creating(function ($model) {
            $model->secidno = now()->format('Y') . str_pad($model->count() + 1, 3, '0', STR_PAD_LEFT);
            $model->secrefno = now()->format('mdYHis') . 'TASK';
        });
    }

    protected function fullName()
    {
        return Attribute::make(
            get: fn () => "{$this->seclname}, {$this->secfname} {$this->secmname}"
        );
    }
}
