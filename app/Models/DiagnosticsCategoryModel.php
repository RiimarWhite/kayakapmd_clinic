<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticsCategoryModel extends Model
{
    protected $table = 'diagnostic_category';

    protected $fillable = [
        'category_refno',
        'category_name',
    ];
}
