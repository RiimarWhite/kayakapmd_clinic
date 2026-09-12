<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\XmlEnlistUploading;
use App\Services\Enlistment\EnlistmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnlistmentApiController extends Controller
{
    private EnlistmentService $enlistmentService;

    public function __construct(EnlistmentService $enlistmentService)
    {
        $this->enlistmentService = $enlistmentService;
    }

    public function fetchUploads()
    {
        try {
            $uploads = XmlEnlistUploading::query()
                ->select('UPLOAD_ID', 'dw_clientcode', 'UPLOAD_ID as id', 'DATE_UPLOADED', 'RANGE_DATE', 'status', 'importedby', 'imported')
                ->orderBy('DATE_UPLOADED', 'desc')
                ->paginate(10);

            return response()->json($uploads);
        } catch (\Exception $e) {
            Log::error('Error fetching enlistment uploads: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to fetch enlistment uploads'], 500);
        }
    }

    public function parseXml(Request $request)
    {
        try {
            $uploadId = $request->input('upload_id');
            $result = $this->enlistmentService->parseXmlFromUpload($uploadId);

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            Log::error('Error parsing XML: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to parse XML: ' . $e->getMessage()], 400);
        }
    }

    public function saveEnlistment(Request $request)
    {
        try {
            $uploadId = $request->input('upload_id');
            $result = $this->enlistmentService->saveEnlistmentFromXml($uploadId);

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            Log::error('Error saving enlistment: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to save enlistment: ' . $e->getMessage()], 400);
        }
    }
}
