<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationAnswerModel extends Model
{
    protected $table = 'walkinconsultation_answer';

    protected $fillable = [
        'walkinconsuanswerrefno',
        'questionrefno',
        'answer',
        'pxconsultationrefno',
        'transactedby',
        'transacteddate'
    ];

    public $timestamps = false;
}
