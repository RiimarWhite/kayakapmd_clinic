<?php

namespace App\Models\Profiles;

use App\Models\EnlistmentModel;
use App\Models\ProfileModel;
use Illuminate\Database\Eloquent\Model;

class ProfilePeMiscModel extends Model
{
    protected $table = 'dd_profile_pemisc';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    protected $fillable = [
        'dw_clientcode',
        'px_pin',
        'px_consultcode_cn',
        'p_TransNo',
        'en_caseno',
        'dSkinId',
        'dHeentId',
        'dChestId',
        'dHeartId',
        'dAbdomenId',
        'dNeuroId',
        'dRectalId',
        'dGuId',
        'dReportStatus',
        'dDeficiencyRemarks',
        'createdby',
        'created',
        'updatedby',
        'updated',
    ];

    public function profile()
    {
        return $this->belongsTo(ProfileModel::class, 'p_TransNo', 'dTransNo');
    }

    public function enlistment()
    {
        return $this->belongsTo(EnlistmentModel::class, 'en_caseno', 'dCaseNo');
    }
}
