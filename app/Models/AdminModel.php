<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminModel extends Authenticatable
{
    protected $table = 'adminrights';

    protected $fillable = [
        'adminrefno',
        'username',
        'password' 
    ];

    protected function casts(): array 
    {
        return [
            'password' => 'hashed'
        ];
    }
}
