<?php

namespace App\Http\Controllers;

use App\Models\AdminModel;
use App\Models\DoctorModel;
use App\Models\KayakapProfileModel;
use App\Models\SecretaryModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function index()
    {
        // Detailed Comment: Redirect already-authenticated users directly to their respective dashboards
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin');
        }
        if (Auth::guard('doctor')->check()) {
            return redirect()->route('doctor');
        }
        if (Auth::guard('secretary')->check()) {
            return redirect()->route('secretary');
        }

        return view('login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);

        $facilityClientCode = config('app.client_code', env('CLIENT_CODE', '122377'));

        // Detailed Comment: Structured diagnostic logging for incoming authentication attempt
        Log::info('Authentication attempt initiated', [
            'username' => $credentials['username'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Detailed Comment: Multi-field Secretary lookup supporting username, last name (seclname), ID number (secidno), and email (secemail)
        $secretary = SecretaryModel::where(function ($query) use ($credentials) {
            $query->where('username', $credentials['username'])
                  ->orWhere('seclname', $credentials['username'])
                  ->orWhere('secidno', $credentials['username'])
                  ->orWhere('secemail', $credentials['username']);
        })->first();

        if ($secretary && Hash::check($credentials['password'], $secretary->secpassword)) {
            Auth::guard('secretary')->login($secretary);

            $secClientCode = $secretary->clientcode ?: $facilityClientCode;

            $request->session()->regenerate();
            $request->session()->put('clientcode', $secClientCode);

            $this->setSession($request, $secClientCode);

            Log::info('Secretary authenticated successfully', [
                'id' => $secretary->id,
                'username' => $credentials['username'],
                'secrefno' => $secretary->secrefno,
                'clientcode' => $secClientCode
            ]);

            return redirect()->route('secretary');
        }

        // Detailed Comment: Multi-field Doctor lookup supporting username, last name (doclname), email (eadd), and reference number (docrefno)
        $doctor = DoctorModel::where(function ($query) use ($credentials) {
            $query->where('username', $credentials['username'])
                  ->orWhere('doclname', $credentials['username'])
                  ->orWhere('eadd', $credentials['username'])
                  ->orWhere('docrefno', $credentials['username']);
        })->first();

        if ($doctor && Hash::check($credentials['password'], $doctor->pass)) {
            Auth::guard('doctor')->login($doctor);

            $docClientCode = $doctor->dw_clientcode ?: $facilityClientCode;

            $request->session()->regenerate();
            $request->session()->put('clientcode', $docClientCode);

            $this->setSession($request, $docClientCode);

            Log::info('Doctor authenticated successfully', [
                'id' => $doctor->id,
                'username' => $credentials['username'],
                'docrefno' => $doctor->docrefno,
                'clientcode' => $docClientCode
            ]);

            return redirect()->route('doctor');
        }

        // Detailed Comment: Admin lookup matching admin username
        $admin = AdminModel::where('username', $credentials['username'])->first();
        if ($admin && Hash::check($credentials['password'], $admin->password)) {
            Auth::guard('admin')->login($admin);

            $adminClientCode = $admin->clientcode ?: $facilityClientCode;

            $request->session()->regenerate();
            $request->session()->put('clientcode', $adminClientCode);

            $this->setSession($request, $adminClientCode);

            Log::info('Admin authenticated successfully', [
                'id' => $admin->id,
                'username' => $credentials['username'],
                'clientcode' => $adminClientCode
            ]);

            return redirect()->route('admin');
        }

        // Detailed Comment: Log failure when credentials match no active guard
        Log::warning('Authentication failed: invalid credentials', [
            'username' => $credentials['username'],
            'ip' => $request->ip()
        ]);

        return back()->withErrors([
            'password' => 'Invalid credentials.',
        ])->onlyInput('password');
    }

    public function logout(Request $request)
    {
        $loggedOutGuards = [];

        // Detailed Comment: Invalidate all configured authentication guards without early termination
        foreach (array_keys(config('auth.guards')) as $guard) {
            if (Auth::guard($guard)->check()) {
                $loggedOutGuards[] = $guard;
                Auth::guard($guard)->logout();
            }
        }

        // Detailed Comment: Clear all session attributes and regenerate CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User signed out', [
            'guards' => $loggedOutGuards,
            'ip' => $request->ip()
        ]);

        // Detailed Comment: Return JSON response for AJAX requests with target redirect URL
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('login'),
            ]);
        }

        // Detailed Comment: Fall back to standard HTTP 302 redirect to the named login route
        return redirect()->route('login');
    }

    private function setSession(Request $request, $clientCode)
    {
        $request->session()->save();

        DB::table('sessions')
            ->where('id', $request->session()->getId())
            ->update(['clientcode' => $clientCode]);
    }
}
