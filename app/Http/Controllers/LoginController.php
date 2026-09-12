<?php

namespace App\Http\Controllers;

use App\Models\AdminModel;
use App\Models\DoctorModel;
use App\Models\KayakapProfileModel;
use App\Models\SecretaryModel;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);

        $secretary = SecretaryModel::where('seclname', $credentials['username'])->first();
        if ($secretary && Hash::check($credentials['password'], $secretary->secpassword)) {
            Auth::guard('secretary')->login($secretary);

            $request->session()->regenerate();
            $request->session()->put('clientcode', $secretary->clientcode);

            $this->setSession($request, $secretary->clientcode);

            return redirect()->route('secretary');
        }

        $doctor = DoctorModel::where('username', $credentials['username'])->first();
        if ($doctor && Hash::check($credentials['password'], $doctor->pass)) {
            Auth::guard('doctor')->login($doctor);

            $request->session()->regenerate();
            $request->session()->put('clientcode', $doctor->clientcode);

            $this->setSession($request, $doctor->dw_clientcode);

            return redirect()->route('doctor');
        }

        $admin = AdminModel::where('username', $credentials['username'])->first();
        if ($admin && Hash::check($credentials['password'], $admin->password)) {
            Auth::guard('admin')->login($admin);

            $request->session()->regenerate();
            $request->session()->put('clientcode', $admin->clientcode);

            $this->setSession($request, $admin->clientcode);

            return redirect()->route('admin');
        }

        return back()->withErrors([
            'password' => 'Invalid credentials.',
        ])->onlyInput('password');
    }

    public function logout(Request $request)
    {
        foreach (array_keys(config('auth.guards')) as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
                break;
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function setSession(Request $request, $clientCode)
    {
        $request->session()->save();

        DB::table('sessions')
            ->where('id', $request->session()->getId())
            ->update(['clientcode' => $clientCode]);
    }
}
