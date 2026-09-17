<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;

class DoctorModel extends Authenticatable
{
    protected $table = 'doctorsrights';

    // Detailed Comment: Explicitly define the primary key matching the database schema
    protected $primaryKey = 'id';

    protected $fillable = [
        'dw_clientcode',
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

    /**
     * Detailed Comment: Implement Laravel Authenticatable password getter for the legacy 'pass' column.
     * Guarantees internal guard credential verification and session password hashing work correctly.
     */
    public function getAuthPassword(): ?string
    {
        return $this->pass;
    }

    /**
     * Detailed Comment: Explicitly specify 'pass' as the auth password attribute name.
     */
    public function getAuthPasswordName(): string
    {
        return 'pass';
    }

    /**
     * Detailed Comment: Accessor to seamlessly map 'clientcode' to 'dw_clientcode'.
     * Prevents null values when controllers or middleware access $doctor->clientcode.
     */
    public function getClientcodeAttribute(): ?string
    {
        return $this->attributes['dw_clientcode'] ?? null;
    }

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
