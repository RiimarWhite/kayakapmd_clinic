<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;

class SecretaryModel extends Authenticatable
{
    protected $table = 'secretaryrights';

    // Detailed Comment: Explicitly declare the primary key matching the repaired schema
    protected $primaryKey = 'id';

    protected $fillable = [
        'secrefno',
        'secidno',
        'username',
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
        'verified',
        'clientcode'
    ];

    protected $hidden = [
        'secpassword'
    ];

    public $timestamps = false;

    /**
     * Detailed Comment: Implement Laravel Authenticatable password getter for legacy 'secpassword' column.
     * Ensures session guard password validation and credential verification execute seamlessly.
     */
    public function getAuthPassword(): ?string
    {
        return $this->secpassword;
    }

    /**
     * Detailed Comment: Explicitly specify 'secpassword' as the auth password attribute name.
     */
    public function getAuthPasswordName(): string
    {
        return 'secpassword';
    }

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
