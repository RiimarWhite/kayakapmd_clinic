<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HMOModel;
use App\Services\YakapManagement\ApiService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;

/**
 * Detailed Comment: Controller handling PhilHealth e-Claims API integration requests.
 * Proxies calls to ApiService with structured error handling and standardized JSON responses.
 */
class PhilHealthApiController extends Controller implements HasMiddleware
{
    private $apiService;

    /**
     * Detailed Comment: Laravel 11 controller middleware definition.
     * Enforces feature toggle check so that PhilHealth integration endpoints return 403 when disabled in .env.
     */
    public static function middleware(): array
    {
        return [
            function ($request, $next) {
                if (!config('services.philhealth.enabled', true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'PhilHealth integration is currently disabled in system settings.'
                    ], 403);
                }
                return $next($request);
            }
        ];
    }

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Detailed Comment: Authenticates with PhilHealth e-Claims web service and returns an auth token.
     */
    public function generateToken()
    {
        try {
            $data = $this->apiService->getToken();
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Token retrieved successfully.'
            ]);
        } catch (\Throwable $e) {
            Log::error('PhilHealthApiController@generateToken exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to obtain PhilHealth token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detailed Comment: Checks if a member/dependent is registered under PhilHealth.
     */
    public function checkRegistration(Request $request)
    {
        $request->validate([
            'pin' => 'required',
            'type' => 'nullable'
        ]);

        try {
            $data = $this->apiService->isMemberDependentRegistered([
                'pin' => $request->pin,
                'type' => $request->type ?? 'M',
                'token' => $request->token
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            Log::error('PhilHealthApiController@checkRegistration exception', [
                'pin' => $request->pin,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Registration check failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detailed Comment: Fetches registration list between two dates from PhilHealth.
     */
    public function fetchRegistrations(Request $request)
    {
        try {
            $data = $this->apiService->extractRegistrationList([
                'start' => $request->start ?? now()->startOfMonth()->format('Y-m-d'),
                'end' => $request->end ?? now()->format('Y-m-d'),
                'token' => $request->token
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            Log::error('PhilHealthApiController@fetchRegistrations exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch registration list: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detailed Comment: Validates Authorization to Consult (ATC) for patient PIN and effectivity date.
     */
    public function checkATC(Request $request)
    {
        $request->validate([
            'pin' => 'required',
            'atc' => 'required',
            'effectivity_date' => 'nullable'
        ]);

        try {
            $data = $this->apiService->isATCValid([
                'pin' => $request->pin,
                'atc' => $request->atc,
                'effectivity_date' => $request->effectivity_date ?? now()->format('Y-m-d'),
                'token' => $request->token
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            Log::error('PhilHealthApiController@checkATC exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'ATC validation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detailed Comment: Validates XML report transmittal payload against PhilHealth schemas.
     */
    public function validateXML(Request $request)
    {
        $request->validate([
            'report' => 'required',
            'tranche' => 'nullable'
        ]);

        try {
            $data = $this->apiService->validateReport([
                'report' => $request->report,
                'tranche' => $request->tranche ?? '1',
                'token' => $request->token
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            Log::error('PhilHealthApiController@validateXML exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Report validation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detailed Comment: Submits an encrypted XML report transmittal to PhilHealth.
     */
    public function submitXML(Request $request)
    {
        $request->validate([
            'report' => 'required',
            'transmittal_id' => 'nullable',
            'tranche' => 'nullable'
        ]);

        try {
            $data = $this->apiService->submitReport([
                'transmittal_id' => $request->transmittal_id ?? ('TR-' . now()->format('YmdHis')),
                'report' => $request->report,
                'tranche' => $request->tranche ?? '1',
                'token' => $request->token
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Report submitted successfully to PhilHealth.'
            ]);
        } catch (\Throwable $e) {
            Log::error('PhilHealthApiController@submitXML exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Report submission failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
