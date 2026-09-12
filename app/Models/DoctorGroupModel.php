<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorGroupModel extends Model
{
    protected $table = 'doctors_groups';

    protected $fillable = [
        'name'
    ];

    public function doctors() {
        return $this->hasMany(DoctorModel::class, 'specialization');
    }
}
