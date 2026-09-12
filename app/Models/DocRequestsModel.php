<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocRequestsModel extends Model
{
    protected $table = 'docrequests';

    protected $fillable = [
        'consultationrefno',
        'docrefno',
        'requestrefno',
        'request_dscr',
        'request_catg',
    ];
}
