<?php

namespace App\Http\Controllers;

use App\Models\ConsultationModel;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function register(Request $request) {
        ConsultationModel::create([
            'patientname' => $request->pFname,
            'pxmidname' => $request->pMname,
            'pxlastname' => $request->pLname,
            'pxsuffix' => $request->pExtname,
            'gender' => $request->psex,
            'birthday' => $request->pDob,
            'memFname' => $request->pMemFname,
            'memMname' => $request->pMemMname,
            'memLname' => $request->pMemLname,
            'memExtname' => $request->pMemExtname,
            'mobilenumber' => $request->pMobileNumber,
            'emailaddress' => $request->pEmail,
            'address' => $request->pAddress,
        ]);
    }
}
