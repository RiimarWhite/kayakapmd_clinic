<?php

namespace App\Models\Profiles;

use App\Models\EnlistmentModel;
use App\Models\ProfileModel;
use Illuminate\Database\Eloquent\Model;

class ProfileNcdQansModel extends Model
{
    protected $table = 'dd_profile_ncdqans';

    const CREATED_AT = 'created';

    const UPDATED_AT = 'updated';

    protected $fillable = [
        'dw_clientcode',
        'px_pin',
        'px_consultcode_cn',
        'p_TransNo',
        'en_caseno',
        'dQid1_Yn',
        'dQid2_Yn',
        'dQid3_Yn',
        'dQid4_Yn',
        'dQid5_Ynx',
        'dQid6_Yn',
        'dQid7_Yn',
        'dQid8_Yn',
        'dQid9_Yn',
        'dQid10_Yn',
        'dQid11_Yn',
        'dQid12_Yn',
        'dQid13_Yn',
        'dQid14_Yn',
        'dQid15_Yn',
        'dQid16_Yn',
        'dQid17_abcde',
        'dQid18_Yn',
        'dQid19_Yn',
        'dQid19_Fbsmg',
        'dQid19_Fbsmmol',
        'dQid19_Fbsdate',
        'dQid20_Yn',
        'dQid20_Choleval',
        'dQid20_Choledate',
        'dQid21_Yn',
        'dQid21_Ketonval',
        'dQid21_Ketondate',
        'dQid22_Yn',
        'dQid22_Proteinval',
        'dQid22_Proteindate',
        'dQid23_Yn',
        'dQid24_Yn',
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
