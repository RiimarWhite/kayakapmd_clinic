<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Detailed Comment: AdminVerificationController manages session-based elevation of privileges
 * for sensitive administrative operations (such as fee modifications, masterlist alterations,
 * and queue record deletions). Supports customizable elevation durations (1 hour, 2 hours, 1 day)
 * and invalidates upon session expiration or logout.
 */
class AdminVerificationController extends Controller
{
    /**
     * Detailed Comment: Helper method to determine if current request context has valid admin elevation.
     * Accessible by any controller to guard sensitive actions (fee editing, deletion, etc.).
     */
    public static function isUserElevated(Request $request): bool
    {
        if (Auth::guard('admin')->check()) {
            return true;
        }

        $isVerified = $request->session()->get('admin_verified') === true;
        $expiresAt = $request->session()->get('admin_verified_expires_at');
        $currentTime = now()->timestamp;

        return $isVerified && $expiresAt && $expiresAt > $currentTime;
    }

    /**
     * Detailed Comment: Checks whether the current user session holds valid, unexpired admin elevation.
     * Logged-in admin guards automatically satisfy elevation; secretary sessions check session timestamps.
     */
    public function checkAdminElevation(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return response()->json([
                'elevated' => true,
                'is_admin' => true,
                'message' => 'Authenticated as Administrator.'
            ]);
        }

        $isVerified = $request->session()->get('admin_verified') === true;
        $expiresAt = $request->session()->get('admin_verified_expires_at');
        $currentTime = now()->timestamp;

        if ($isVerified && $expiresAt && $expiresAt > $currentTime) {
            return response()->json([
                'elevated' => true,
                'is_admin' => false,
                'expires_at' => $expiresAt,
                'remaining_seconds' => $expiresAt - $currentTime
            ]);
        }

        // Detailed Comment: If expired, clean up elevation session keys
        if ($isVerified) {
            $request->session()->forget(['admin_verified', 'admin_verified_username', 'admin_verified_expires_at']);
        }

        return response()->json([
            'elevated' => false,
            'is_admin' => false
        ]);
    }

    /**
     * Detailed Comment: Verifies provided admin credentials and elevates session with selected duration.
     * Duration options: 1 hour, 2 hours, 1 day.
     */
    public function verifyAdminCredentials(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'remember_access' => 'nullable',
            'duration' => 'nullable|in:1_hour,2_hours,1_day'
        ]);

        $username = trim($request->input('username'));
        $password = $request->input('password');
        $rememberAccess = $request->boolean('remember_access') || $request->input('remember_access') === '1';
        $duration = $request->input('duration', '1_hour');

        // Detailed Comment: Query admin by username or admin email
        $admin = AdminModel::where(function ($query) use ($username) {
            $query->where('username', $username)
                  ->orWhere('adminemail', $username);
        })->first();

        if (!$admin || !Hash::check($password, $admin->password)) {
            Log::warning('Admin credential elevation attempt rejected: invalid credentials', [
                'attempted_username' => $username,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid administrator username or password.'
            ], 401);
        }

        // Detailed Comment: Calculate expiration timestamp based on selected duration
        $expiresAt = match ($duration) {
            '2_hours' => now()->addHours(2)->timestamp,
            '1_day' => now()->addDay()->timestamp,
            default => now()->addHour()->timestamp,
        };

        // If remember_access is unchecked, grant a single-action grace window of 5 minutes
        if (!$rememberAccess) {
            $expiresAt = now()->addMinutes(5)->timestamp;
        }

        $request->session()->put('admin_verified', true);
        $request->session()->put('admin_verified_username', $admin->username);
        $request->session()->put('admin_verified_expires_at', $expiresAt);

        Log::info('Admin credential elevation granted successfully', [
            'admin_username' => $admin->username,
            'duration' => $rememberAccess ? $duration : 'single_action_5min',
            'expires_at' => $expiresAt,
            'ip' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Administrator authorization verified successfully.',
            'expires_at' => $expiresAt,
            'duration' => $rememberAccess ? $duration : 'single_action'
        ]);
    }
}
