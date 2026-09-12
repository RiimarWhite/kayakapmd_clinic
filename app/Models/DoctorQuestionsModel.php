<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorQuestionsModel extends Model
{
    protected $table = 'docquestion';

    protected $fillable = [
        'docquestionrefno',
        'secrefno',
        'question',
        'docrefno',
        'docname',
        'recordeddate',
        'recordedby'
    ];

    public $timestamps = false;
}
