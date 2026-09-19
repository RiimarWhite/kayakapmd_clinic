<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminModel extends Authenticatable
{
    // Detailed Comment: Explicitly define the table and primary key matching schema
    protected $table = 'adminrights';
    protected $primaryKey = 'id';

    /**
     * Detailed Comment: Comprehensive fillable attributes for administrative credentials and contact profile.
     */
    protected $fillable = [
        'adminrefno',
        'username',
        'adminfname',
        'adminmname',
        'adminlname',
        'admincontactno',
        'adminemail',
        'password',
        'clientcode'
    ];

    protected function casts(): array 
    {
        return [
            'password' => 'hashed'
        ];
    }
}
