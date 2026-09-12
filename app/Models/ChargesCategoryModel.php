<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChargesCategoryModel extends Model
{
    protected $table = 'charges_category';

    protected $fillable = [
        'categoryrefno',
        'categoryname'
    ];
}
