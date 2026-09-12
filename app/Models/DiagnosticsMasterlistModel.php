<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticsMasterlistModel extends Model
{
    protected $table = 'diagnostics_masterlist';

    protected $fillable = [
        'diagnosticrefno',
        'diagnostic_name',
        'diagnostic_catg'
    ];

    public function category() {
        return $this->belongsTo(
            DiagnosticsCategoryModel::class,
            'diagnostic_catg',
            'category_refno'
        );
    }
}
