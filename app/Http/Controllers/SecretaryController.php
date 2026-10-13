<?php

namespace App\Http\Controllers;

use App\Models\HMOModel;
use App\Models\SecretaryModel;
use Date;
use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ConsultationModel;
use App\Models\ConsultationAnswerModel;
use App\Models\DoctorQuestionsModel;
use App\Models\DoctorsProfileModel;
use App\Models\ScheduleModel;
use App\Models\SecretaryDoctorsModel;
use App\Models\SettlementsModel;
use App\Models\ServicesGroupManagementModel;
use App\Models\ServicesManagementModel;
use App\Models\Stocks\StocksLedgerModel;
use App\Models\PatientMasterlist;
use App\Models\PxMedicalHistoryModel;
use App\Models\KayakapProfileModel;
// Detailed Comment: Import legacy clinical models (pxrxdocuments, docrequests, diagnostics_masterlist, doccharges, stocks_listing)
// to support defensive fallback and data synchronization for historical consultations in fetchPatientMedhistory
use App\Models\DocChargesModel;
use App\Models\DocRequestsModel;
use App\Models\DoctorMedicinesModel;
use App\Models\DiagnosticsMasterlistModel;
use App\Models\Stocks\StocksListingModel;
use App\Models\Libraries\MDiseaseLibModel;
use App\Models\ProfileModel;
use App\Models\Profiles\ProfileMedHistModel;
use App\Models\Profiles\ProfileMhSpecificModel;
use App\Models\Profiles\ProfileSurgHistModel;
use App\Services\ClientService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SecretaryController extends Controller
{
    public function index()
    {
        return redirect()->route('secretary.queue');
    }

    public function queuePage()
    {
        // Detailed Comment: Resolve authenticated user across secretary and admin guards.
        // For admin users accessing the secretary queue console, load all active doctors so the dropdown
        // and queue filtering operate without restriction, while secretaries only see assigned doctors.
        $secUser = auth()->guard('secretary')->user();
        $isAdmin = auth()->guard('admin')->check();

        if ($isAdmin) {
            $doctors = DoctorsProfileModel::select(['docrefno', 'docname'])->get();
        } else {
            $assigned_doctors = $secUser ? SecretaryDoctorsModel::where([
                'secrefno' => $secUser->secrefno
            ])->pluck('docrefno') : collect();

            $doctors = DoctorsProfileModel::select(['docrefno', 'docname'])
                ->whereIn('docrefno', $assigned_doctors)
                ->get();
        }

        $secretaries = SecretaryModel::select(['secrefno', 'seclname', 'secfname'])->get();

        // Detailed Comment: Load active disease library entries for Yakap Medical History/Profiling modal checklist
        $mDiseaseLibrary = MDiseaseLibModel::where('lib_stat', 1)
            ->orderBy('sort_no')
            ->get();

        // Detailed Comment: Structured logging when secretary queue page is rendered
        Log::info('Secretary queue page rendered', [
            'is_admin' => $isAdmin,
            'secretary_id' => $secUser ? $secUser->id : null,
            'secrefno' => $secUser ? $secUser->secrefno : null,
            'assigned_doctors_count' => $doctors->count(),
            'mdisease_count' => $mDiseaseLibrary->count(),
        ]);

        return view('pages.secretary.queue', [
            'doctors' => $doctors,
            'secretaries' => $secretaries,
            'mDiseaseLibrary' => $mDiseaseLibrary,
        ]);
    }

    //for loading new reference and patient codes
    public function generateNewCodes()
    {
        return response()->json([
            'refCode' => $this->generateReferenceCode(),
            'patientCode' => $this->generatePatientCode()
        ]);
    }

    private function generateReferenceCode()
    {
        // Set timezone to Philippines
        $now = Carbon::now('Asia/Manila');

        // Format: CRNW + MMDDYYYY + HHMMSS
        $refCode = 'CRNW' . $now->format('mdYHis');

        return $refCode;
    }

    private function generatePatientCode()
    {
        // Set timezone to Philippines
        $now = Carbon::now('Asia/Manila');

        // Format: PTNW + MMDDYYYY + HHMMSS
        $patientCode = 'PTN' . $now->format('YHis');

        return $patientCode;
    }

    // Management
    public function management()
    {
        return view('secretary.management');
    }

    public function fetchDoctorsFromSecretary()
    {
        // Detailed Comment: Safely retrieve assigned doctors for secretary login,
        // or return all doctors if an administrator is requesting doctors for queue filtering.
        $secUser = auth()->guard('secretary')->user();
        if ($secUser) {
            $assigned_doctors = SecretaryDoctorsModel::where(['secrefno' => $secUser->secrefno])->pluck('docrefno');
            $doctors = DoctorsProfileModel::select(['docrefno', 'docname'])->whereIn('docrefno', $assigned_doctors)->get();
        } else {
            $doctors = DoctorsProfileModel::select(['docrefno', 'docname'])->get();
        }

        return response()->json(['success' => true, 'doctors' => $doctors]);
    }

    // Question-related
    // Detailed Comment: Fetch doctor questions with flexible parameter support (docrefno or docid) and dual response keys
    public function fetchQuestions(Request $request, $docid = null)
    {
        $refno = $request->input('docrefno') ?: $request->input('docid') ?: $docid ?: $request->route('docid');
        $docquestions = DoctorQuestionsModel::select(['docquestionrefno', 'question', 'docname', 'docrefno', 'recordeddate', 'recordedby'])
            ->where('docrefno', $refno)
            ->get();

        return response()->json([
            'success' => true,
            'docquestions' => $docquestions,
            'questions' => $docquestions
        ]);
    }

    // Detailed Comment: Alias for fetching questions by doctor reference/ID for contract parity
    public function fetchDoctorQuestions(Request $request, $docid = null)
    {
        return $this->fetchQuestions($request, $docid);
    }

    // Detailed Comment: Fetch a single doctor question by docquestionrefno or ID
    public function fetchQuestionByRef(Request $request, $id = null)
    {
        $refno = $request->input('docquestionrefno') ?: $request->input('id') ?: $id ?: $request->route('id');
        $question = DoctorQuestionsModel::where('docquestionrefno', $refno)->first();
        if (!$question) {
            return response()->json(['success' => false, 'message' => 'Doctor question not found.'], 404);
        }

        return response()->json(['success' => true, 'question' => $question]);
    }

    // Detailed Comment: Create a new doctor question; authorizes both admin and secretary guards, resolves doctor full name, and returns clean 404 on missing doctor
    public function createQuestion(Request $request)
    {
        $docrefno = $request->input('docrefno') ?: $request->input('docid');
        if ($docrefno) {
            $request->merge(['docrefno' => $docrefno]);
        }

        $request->validate([
            'docrefno' => 'required|string',
            'question' => 'required|string',
        ]);

        $user = auth()->guard('admin')->user() ?? auth()->guard('secretary')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $doctor = DoctorsProfileModel::where('docrefno', $request->docrefno)->first();
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor profile not found.'], 404);
        }

        // Detailed Comment: Resolve doctor full name using real schema columns (docname or docfname, docmname, doclname, suffix)
        $docname = $doctor->docname ?: trim(implode(' ', array_filter([$doctor->docfname, $doctor->docmname, $doctor->doclname, $doctor->suffix])));
        if (!$docname) {
            $docname = 'Doctor';
        }

        $secrefno = $user->adminrefno ?? $user->secrefno ?? 'ADMIN';
        $recordedby = $user->username ?? 'admin';

        $question = DoctorQuestionsModel::create([
            'docquestionrefno' => Date::now()->format('mdYHis') . "QT",
            'secrefno' => $secrefno,
            'question' => $request->question,
            'docrefno' => $request->docrefno,
            'docname' => $docname,
            'recordeddate' => Date::now(),
            'recordedby' => $recordedby,
        ]);

        Log::info('Doctor question created', [
            'docquestionrefno' => $question->docquestionrefno,
            'docrefno' => $request->docrefno,
            'recordedby' => $recordedby,
        ]);

        return response()->json(['success' => true, 'question' => $question]);
    }

    // Detailed Comment: Edit an existing doctor question by reference number with authentication and 404 handling
    public function editQuestion(Request $request)
    {
        $refno = $request->input('docquestionrefno') ?: $request->input('id') ?: $request->route('id');
        if ($refno) {
            $request->merge(['docquestionrefno' => $refno]);
        }

        $request->validate([
            'docquestionrefno' => 'required|string',
            'question' => 'required|string',
        ]);

        $user = auth()->guard('admin')->user() ?? auth()->guard('secretary')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $question = DoctorQuestionsModel::where('docquestionrefno', $request->docquestionrefno)->first();
        if (!$question) {
            return response()->json(['success' => false, 'message' => 'Question not found.'], 404);
        }

        $question->update([
            'question' => $request->question,
        ]);

        Log::info('Doctor question updated', [
            'docquestionrefno' => $request->docquestionrefno,
            'updatedby' => $user->username ?? 'admin',
        ]);

        return response()->json(['success' => true, 'question' => $question]);
    }

    // Detailed Comment: Delete a doctor question by reference number or route ID with authentication and 404 handling
    public function deleteQuestion(Request $request, $id = null)
    {
        $refno = $request->input('docquestionrefno') ?: $request->input('id') ?: $id ?: $request->route('id');
        if ($refno) {
            $request->merge(['docquestionrefno' => $refno]);
        }

        $request->validate([
            'docquestionrefno' => 'required|string',
        ]);

        $user = auth()->guard('admin')->user() ?? auth()->guard('secretary')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $question = DoctorQuestionsModel::where('docquestionrefno', $request->docquestionrefno)->first();
        if (!$question) {
            return response()->json(['success' => false, 'message' => 'Question not found.'], 404);
        }

        $question->delete();

        Log::info('Doctor question deleted', [
            'docquestionrefno' => $request->docquestionrefno,
            'deletedby' => $user->username ?? 'admin',
        ]);

        return response()->json(['success' => true]);
    }

    // Detailed Comment: Schedule-related operations for physicians
    public function fetchSchedules(Request $request, $docid = null)
    {
        $refno = $request->input('docrefno') ?: $request->input('docid') ?: $docid ?: $request->route('docid');
        if ($refno) {
            $request->merge(['docrefno' => $refno]);
        }

        $request->validate([
            'docrefno' => 'required|string'
        ]);

        // Detailed Comment: Order by natural day of the week (Sunday through Saturday), then by start time using ANSI SQL CASE
        $dayOrderSql = "CASE day
            WHEN 'Sunday' THEN 1
            WHEN 'Monday' THEN 2
            WHEN 'Tuesday' THEN 3
            WHEN 'Wednesday' THEN 4
            WHEN 'Thursday' THEN 5
            WHEN 'Friday' THEN 6
            WHEN 'Saturday' THEN 7
            ELSE 8
        END ASC, start ASC";

        $schedules = ScheduleModel::where('docrefno', $request->docrefno)
            ->orderByRaw($dayOrderSql)
            ->get();

        return response()->json([
            'success' => true,
            'schedules' => $schedules
        ]);
    }

    // Detailed Comment: Parity alias for fetching doctor schedules
    public function fetchDoctorSchedules(Request $request, $docid = null)
    {
        return $this->fetchSchedules($request, $docid);
    }

    /**
     * Detailed Comment: Fetch a doctor's clinic time slots for the weekday of the requested consultation date.
     * Used by the Secretary Console (admin + secretary) to populate #stime, #sched_time and #resched_time.
     *
     * Fix (secretary console unable to detect doctor schedules):
     * - When no doctor is selected ("None" in #doctor_id) the request used to run a query with a NULL docrefno and
     *   silently return an empty list, which the UI rendered as the misleading "No schedules set.". We now return
     *   `doctor_selected = false` so the UI can prompt the user to pick a doctor instead.
     * - An unparsable date previously threw an uncaught exception (HTTP 500); it now falls back to today.
     * - The response also carries `weekday` and the doctor's distinct `available_days`, so when the doctor simply
     *   has no clinic on the chosen weekday the UI can say e.g. "No clinic on Monday (available: Saturday)".
     */
    public function fetchSchedulesSpecific(Request $request)
    {
        $docrefno = trim((string) $request->input('docrefno', ''));

        // Detailed Comment: Parse the consultation date defensively; fall back to today's date on invalid input.
        try {
            $consulDate = $request->consuldate ? new DateTime($request->consuldate) : new DateTime();
        } catch (\Exception $e) {
            Log::warning('fetchSchedulesSpecific received an invalid consultation date', [
                'consuldate' => $request->consuldate,
                'error' => $e->getMessage(),
            ]);
            $consulDate = new DateTime();
        }
        $weekday = $consulDate->format('l');

        // Detailed Comment: No doctor selected -> nothing to look up; tell the UI explicitly instead of returning
        // an empty list that is indistinguishable from "doctor has no schedule".
        if ($docrefno === '') {
            return response()->json([
                'success' => true,
                'doctor_selected' => false,
                'weekday' => $weekday,
                'schedules' => [],
                'available_days' => [],
            ]);
        }

        $schedules = ScheduleModel::where([
            'docrefno' => $docrefno,
            'day' => $weekday
        ])->orderBy('start', 'ASC')->get();

        // Detailed Comment: Distinct weekdays on which this doctor holds clinic, ordered Sunday..Saturday,
        // so the console can hint which dates are bookable when the selected weekday has no slots.
        $weekOrder = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $availableDays = ScheduleModel::where('docrefno', $docrefno)
            ->distinct()
            ->pluck('day')
            ->filter()
            ->sortBy(fn ($day) => array_search($day, $weekOrder, true))
            ->values();

        return response()->json([
            'success' => true,
            'doctor_selected' => true,
            'weekday' => $weekday,
            'schedules' => $schedules,
            'available_days' => $availableDays,
        ]);
    }

    // Detailed Comment: To get specific schedule by schedrefno or route parameter
    public function fetchScheduleByRef(Request $request, $schedrefno = null) {
        $refno = $request->input('schedrefno') ?: $request->input('id') ?: $schedrefno ?: $request->route('schedrefno');
        $sched = ScheduleModel::where('schedrefno', $refno)->first();
        if (!$sched) {
            return response()->json(['success' => false, 'message' => 'Schedule not found.'], 404);
        }

        return response()->json(['success' => true, 'sched' => $sched, 'schedule' => $sched]);
    }

    // Detailed Comment: Edit doctor schedule; authenticates admin/secretary and validates existence
    public function editSchedule(Request $request) {
        $user = auth()->guard('admin')->user() ?? auth()->guard('secretary')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $schedrefno = $request->input('schedrefno') ?: $request->input('id');
        if ($schedrefno) {
            $request->merge(['schedrefno' => $schedrefno]);
        }

        $request->validate([
            'schedrefno' => 'required|string',
            'day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start' => 'required',
            'end' => 'required'
        ]);

        $sched = ScheduleModel::where('schedrefno', $request->schedrefno)->first();
        if (!$sched) {
            return response()->json(['success' => false, 'message' => 'Schedule not found.'], 404);
        }

        $updateData = [
            'day' => $request->day,
            'start' => $request->start,
            'end' => $request->end,
        ];
        if ($request->has('notes') && \Illuminate\Support\Facades\Schema::hasColumn('docschedules', 'notes')) {
            $updateData['notes'] = $request->notes;
        }

        $sched->update($updateData);

        Log::info('Doctor schedule updated', [
            'schedrefno' => $request->schedrefno,
            'day' => $request->day,
            'start' => $request->start,
            'end' => $request->end,
            'updatedby' => $user->username ?? 'admin'
        ]);

        return response()->json(['success' => true, 'sched' => $sched, 'schedule' => $sched]);
    }

    // Detailed Comment: Create doctor schedule; authenticates admin/secretary, validates doctor profile, and handles parameter key variations
    public function createSchedule(Request $request)
    {
        $user = auth()->guard('admin')->user() ?? auth()->guard('secretary')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Detailed Comment: Support both frontend modal parameter keys (day, stime, etime) and API keys (schedule_day, sched_from, sched_to)
        $docrefno = $request->input('docrefno') ?: $request->input('docid');
        $day = $request->input('schedule_day') ?: $request->input('day');
        $from = $request->input('sched_from') ?: $request->input('start') ?: $request->input('stime') ?: $request->input('start_time');
        $to = $request->input('sched_to') ?: $request->input('end') ?: $request->input('etime') ?: $request->input('end_time');
        $notes = $request->input('notes');

        $request->merge([
            'docrefno' => $docrefno,
            'schedule_day' => $day,
            'sched_from' => $from,
            'sched_to' => $to
        ]);

        $request->validate([
            'docrefno' => 'required|string',
            'schedule_day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'sched_from' => 'required',
            'sched_to' => 'required',
        ]);

        $doctor = DoctorsProfileModel::where('docrefno', $docrefno)->first();
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor profile not found.'], 404);
        }

        $facilityClientCode = $user->clientcode ?? config('app.client_code', env('CLIENT_CODE', '122377'));
        $schedrefno = Date::now()->format('mdYHis') . 'SCHED' . rand(10, 99);

        $scheduleData = [
            'dw_clientcode' => $facilityClientCode,
            'schedrefno' => $schedrefno,
            'docrefno' => $request->docrefno,
            'day' => $request->schedule_day,
            'start' => $request->sched_from,
            'end' => $request->sched_to,
        ];

        // Detailed Comment: Defensively verify if docschedules table contains notes column before persisting notes
        if ($notes !== null && \Illuminate\Support\Facades\Schema::hasColumn('docschedules', 'notes')) {
            $scheduleData['notes'] = $notes;
        }

        $schedule = ScheduleModel::create($scheduleData);

        // Detailed Comment: Structured logging for schedule creation
        Log::info('Doctor schedule created', [
            'schedrefno' => $schedrefno,
            'docrefno' => $request->docrefno,
            'day' => $request->schedule_day,
            'start' => $request->sched_from,
            'end' => $request->sched_to,
            'createdby' => $user->username ?? 'admin'
        ]);

        return response()->json(['success' => true, 'schedule' => $schedule]);
    }

    // Detailed Comment: Delete doctor schedule; authenticates admin/secretary and deletes by schedrefno or route ID
    public function deleteSchedule(Request $request, $id = null) {
        $user = auth()->guard('admin')->user() ?? auth()->guard('secretary')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $schedrefno = $request->input('schedrefno') ?: $request->input('id') ?: $id ?: $request->route('id');
        if ($schedrefno) {
            $request->merge(['schedrefno' => $schedrefno]);
        }

        $request->validate([
            'schedrefno' => 'required|string'
        ]);

        $sched = ScheduleModel::where('schedrefno', $request->schedrefno)->first();
        if (!$sched) {
            return response()->json(['success' => false, 'message' => 'Schedule not found.'], 404);
        }

        $sched->delete();

        Log::info('Doctor schedule deleted', [
            'schedrefno' => $request->schedrefno,
            'deletedby' => $user->username ?? 'admin'
        ]);

        return response()->json(['success' => true]);
    }

    // Service Group Management

    public function createGroupManagement(Request $request)
    {
        $servicegroup_name = $request->servicegroup_name;
        $servicegroup_dscr = $request->servicegroup_dscr;


        ServicesGroupManagementModel::create([
            'servicegroup_refno' => Date::now()->format('mdYHis') . "GRP",
            "servicegroup_name" => $servicegroup_name,
            "servicegroup_dscr" => $servicegroup_dscr
        ]);

        return response()->json(['success' => true]);
    }

    public function fetchGroupManagement(Request $request)
    {
        $groupManagement = ServicesGroupManagementModel::select(
            'servicegroup_refno',
            'servicegroup_name',
            'servicegroup_dscr'
        )->get()->map(function ($row) {
            return [
                'token' => encrypt($row->servicegroup_refno),
                'servicegroup_refno' => $row->servicegroup_refno,
                'servicegroup_name' => $row->servicegroup_name,
                'servicegroup_dscr' => $row->servicegroup_dscr,
            ];
        });

        return response()->json([
            'groupManagement' => $groupManagement
        ]);
    }

    public function editGroupManagement(Request $request)
    {
        $token = decrypt($request->token);

        if (empty($token)) {
            return response()->json([
                'status' => false,
                'message' => 'Empty Token'
            ]);
        }

        try {
            $result = ServicesGroupManagementModel::where('servicegroup_refno', $token)->first();
            return response()->json([
                'status' => true,
                'message' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function deleteGroupManagement(Request $request)
    {
        try {
            // Decrypt token
            $token = decrypt($request->token);

            if (empty($token)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Empty Token'
                ]);
            }

            // Find the record
            $group = ServicesGroupManagementModel::where('servicegroup_refno', $token)->first();

            if (!$group) {
                return response()->json([
                    'status' => false,
                    'message' => 'Group not found'
                ]);
            }

            // Delete the record
            $group->delete();

            return response()->json([
                'status' => true,
                'message' => 'Group deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateGroupManagement(Request $request)
    {
        try {
            // Decrypt token
            $token = decrypt($request->token);

            if (empty($token)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Empty Token'
                ]);
            }

            // Find the record
            $group = ServicesGroupManagementModel::where('servicegroup_refno', $token)->first();

            if (!$group) {
                return response()->json([
                    'status' => false,
                    'message' => 'Group not found'
                ]);
            }

            // Update the record
            $group->servicegroup_name = $request->servicegroup_name;
            $group->servicegroup_dscr = $request->servicegroup_dscr;
            $group->save(); // saves changes

            return response()->json([
                'status' => true,
                'message' => 'Group updated successfully',
                'data' => $group, // optional: return updated record
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function fetchGroupManagementCategory()
    {
        $groupManagement = ServicesGroupManagementModel::select(
            'servicegroup_refno',
            'servicegroup_name',
            'servicegroup_dscr'
        )->get()->map(function ($row) {
            return [
                'servicegroup_refno' => $row->servicegroup_refno,
                'servicegroup_name' => $row->servicegroup_name,
                'servicegroup_dscr' => $row->servicegroup_dscr,
            ];
        });

        return response()->json([
            'groupManagement' => $groupManagement
        ]);
    }

    public function createServicesManagement(Request $request)
    {
        try {
            // Optional: Validate the request
            $request->validate([
                'servicename' => 'required|string|max:255',
                'servicecharge' => 'required|numeric|min:0',
                'category' => 'required|string|max:255',
                'servicedscr' => 'nullable|string|max:1000',
                'docrefno' => 'required|string|max:50', // or exists:doctors,refno
            ]);

            // Insert into database
            $service = ServicesManagementModel::create([
                'servicerefno' => Date::now()->format('mdYHis') . "SVC", // unique reference
                'servicename' => $request->servicename,
                'servicecharge' => $request->servicecharge,
                'category' => $request->category,
                'servicedscr' => $request->servicedscr,
                'docrefno' => $request->docrefno,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Service added successfully',
                'data' => $service,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function fetchDoctorServices(Request $request)
    {
        // 1️⃣ Fetch all services
        $services = ServicesManagementModel::all();

        // 2️⃣ Fetch all doctors
        $doctors = DoctorsProfileModel::select('docrefno', 'docfname', 'doclname')->get()->keyBy('docrefno');

        // 3️⃣ Map services and attach doctor fullname
        $result = $services->map(function ($service) use ($doctors) {
            $doctor = $doctors->get($service->docrefno);

            return [
                'token' => encrypt($service->servicerefno),
                'servicerefno' => $service->servicerefno,
                'servicename' => $service->servicename,
                'servicedscr' => $service->servicedscr,
                'servicecharge' => $service->servicecharge,
                'category' => $service->category,
                'docrefno' => $service->docrefno,
                'docfullname' => $doctor ? $doctor->docfname . ' ' . $doctor->doclname : 'N/A',
            ];
        });

        // 4️⃣ Return JSON
        return response()->json([
            'doctorServices' => $result
        ]);
    }

    public function editServiceManagement(Request $request)
    {
        try {
            $token = decrypt($request->token);

            if (!$token) {
                return response()->json([
                    'status' => false,
                    'message' => 'Empty token'
                ]);
            }

            $service = ServicesManagementModel::where('servicerefno', $token)->first();

            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found'
                ]);
            }

            return response()->json([
                'status' => true,
                'data' => $service
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid payload'
            ]);
        }
    }

    public function deleteServiceManagement(Request $request)
    {
        try {
            $token = decrypt($request->token);

            $service = ServicesManagementModel::where('servicerefno', $token)->first();

            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found'
                ]);
            }

            $service->delete();

            return response()->json([
                'status' => true,
                'message' => 'Service deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid payload'
            ]);
        }
    }

    public function updateServiceManagement(Request $request)
    {
        try {
            $token = decrypt($request->token);

            if (!$token) {
                return response()->json([
                    'status' => false,
                    'message' => 'Empty token'
                ]);
            }

            $service = ServicesManagementModel::where('servicerefno', $token)->first();

            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found'
                ]);
            }

            // ✅ Update fields
            $service->servicename = $request->servicename;
            $service->servicecharge = $request->servicecharge;
            $service->category = $request->category;
            $service->servicedscr = $request->servicedscr;
            $service->save();

            return response()->json([
                'status' => true,
                'message' => 'Service updated successfully',
                'data' => $service
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid payload'
            ]);
        }
    }

    public function fetchSecretary(Request $request) {
        $user = auth()->guard('secretary')->user();
        if ($user) {
            $user->source_table = 'secretaryrights';
        }

        return response()->json(['success' => true, 'user' => $user]);
    }

    /**
     * Detailed Comment: Self-service profile update for authenticated secretary.
     * Allows secretary to edit their own profile in 'secretaryrights' including username and password,
     * strictly bound to the authenticated secretary's secrefno to prevent cross-user tampering.
     */
    public function updateSecretaryProfile(Request $request) {
        $secAuth = auth()->guard('secretary')->user();
        if (!$secAuth) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated secretary'], 401);
        }

        $request->validate([
            'secfname' => 'required|string|max:100',
            'seclname' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:secretaryrights,username,' . $secAuth->id,
            'secpassword' => 'nullable|string|min:5',
            'secemail' => 'nullable|email|max:100',
            'seccontactno' => 'nullable|string|max:20',
            'secgender' => 'nullable|string|in:male,female,MALE,FEMALE',
            'secbday' => 'nullable|date'
        ]);

        $updateData = [
            'secfname' => $request->secfname,
            'secmname' => $request->secmname,
            'seclname' => $request->seclname,
            'secsuffix' => $request->secsuffix,
            'secgender' => strtoupper($request->secgender ?? $secAuth->secgender ?? 'MALE'),
            'secbday' => $request->secbday,
            'seccontactno' => $request->seccontactno,
            'secemail' => $request->secemail,
            'secadrs' => $request->secadrs,
            'username' => strtolower(trim($request->username))
        ];

        if ($request->filled('secpassword')) {
            $updateData['secpassword'] = Hash::make($request->secpassword);
        }

        SecretaryModel::where('secrefno', $secAuth->secrefno)->update($updateData);

        Log::info('Secretary self-service profile updated', [
            'secrefno' => $secAuth->secrefno,
            'username' => $request->username
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.'
        ]);
    }

    /**
     * Detailed Comment: Fetches comprehensive consultation and medical history for a patient in secretary queue.
     * Returns full consultation modal dataset (vital signs, impressions, diagnosis, admission instructions,
     * prescribed medications, diagnostics requested, lab/radiology files, and charges) to populate the
     * dedicated Consultation Details modal per user requirements.
     */
    public function fetchPatientMedhistory(Request $request) {
        $pincode = $request->input('pincode');
        $pxrefno = $request->input('pxrefno');
        $consultationrefno = $request->input('consultationrefno');
        $patientname = $request->input('patientname');
        $birthday = $request->input('birthday');

        // Detailed Comment: Collect and cross-reference all possible patient identifiers
        // across pxmasterlist and pxwalkinconsultation so that past consultations always appear
        // regardless of whether older records used pincode, pxrefno, or name+birthday.
        $pxrefnoList = [];
        $pincodeList = [];

        if (!empty($pxrefno) && $pxrefno !== 'undefined') {
            $pxrefnoList[] = $pxrefno;
        }
        if (!empty($pincode) && $pincode !== 'undefined') {
            $pincodeList[] = $pincode;
        }

        if (!empty($consultationrefno) && $consultationrefno !== 'undefined') {
            $activeConsult = ConsultationModel::where('consultationrefno', $consultationrefno)->first();
            if ($activeConsult) {
                if (!empty($activeConsult->pxrefno)) $pxrefnoList[] = $activeConsult->pxrefno;
                if (!empty($activeConsult->pincode)) $pincodeList[] = $activeConsult->pincode;
                if (empty($patientname)) $patientname = $activeConsult->patientname;
                if (empty($birthday)) $birthday = $activeConsult->birthday;
            }
        }

        // Cross-reference with pxmasterlist to find associated pincode/pxrefno
        $masterMatch = null;
        if (!empty($pxrefnoList)) {
            $masterMatch = PatientMasterlist::whereIn('pxrefno', $pxrefnoList)->first();
        }
        if (!$masterMatch && !empty($pincodeList)) {
            $masterMatch = PatientMasterlist::whereIn('pincode', $pincodeList)->first();
        }
        if ($masterMatch) {
            if (!empty($masterMatch->pxrefno)) $pxrefnoList[] = $masterMatch->pxrefno;
            if (!empty($masterMatch->pincode)) $pincodeList[] = $masterMatch->pincode;
            if (empty($birthday) && !empty($masterMatch->birthday)) $birthday = $masterMatch->birthday;
            if (empty($patientname) && !empty($masterMatch->patientname)) $patientname = $masterMatch->patientname;
        }

        $pxrefnoList = array_values(array_unique(array_filter($pxrefnoList)));
        $pincodeList = array_values(array_unique(array_filter($pincodeList)));

        $hasIdentifier = !empty($pxrefnoList) || !empty($pincodeList) || (!empty($patientname) && !empty($birthday)) || (!empty($consultationrefno) && $consultationrefno !== 'undefined');

        // Detailed Comment: If no patient identifier was provided, return empty payload safely without querying entire table
        if (!$hasIdentifier) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsFiltered' => 0,
                'recordsTotal' => 0,
                'success' => true,
                'history' => [],
                'medhistory' => [],
                'data' => []
            ]);
        }

        $query = ConsultationModel::query();
        $query->where(function ($sub) use ($pxrefnoList, $pincodeList, $patientname, $birthday, $consultationrefno) {
            $hasAny = false;
            if (!empty($pxrefnoList)) {
                $sub->whereIn('pxrefno', $pxrefnoList);
                $hasAny = true;
            }
            if (!empty($pincodeList)) {
                if ($hasAny) {
                    $sub->orWhereIn('pincode', $pincodeList);
                } else {
                    $sub->whereIn('pincode', $pincodeList);
                    $hasAny = true;
                }
            }
            if (!empty($patientname) && !empty($birthday)) {
                if ($hasAny) {
                    $sub->orWhere(function ($q) use ($patientname, $birthday) {
                        $q->where('patientname', $patientname)->where('birthday', $birthday);
                    });
                } else {
                    $sub->where('patientname', $patientname)->where('birthday', $birthday);
                    $hasAny = true;
                }
            }
            if (!$hasAny && !empty($consultationrefno)) {
                $sub->where('consultationrefno', $consultationrefno);
            }
        });

        // Detailed Comment: Query ConsultationModel with eager-loaded 'patient' (PatientMasterlist) relation.
        // Crucially, 'address' is omitted from select() to resolve MySQL 1054 Unknown column 'address' on pxwalkinconsultation,
        // and is resolved defensively from the authoritative pxmasterlist table during transformation.
        $history = $query->with('patient')
            ->select([
                'id',
                'consultationrefno',
                'pxrefno',
                'pincode',
                'caseno',
                'patientname',
                'gender',
                'birthday',
                'age',
                'mobilenumber',
                'emailaddress',
                // 'address' removed to prevent MySQL 1054 error on pxwalkinconsultation
                'photo_path',
                'consultation_date',
                'reasonforconsultation',
                // Detailed Comment: Include Brief History of Present Illness and Pertinent Past Medical History for consultation details
                'brief_history_present_illness',
                'pertinent_past_medhistory',
                'impression',
                'finadiagnosis',
                'instructions',
                'foradmit',
                'foradmit_instructions',
                'weight',
                'wunit',
                'height',
                'hunit',
                'temp',
                'tempunit',
                'respiratoryrate',
                'pulserate',
                'bpnumerator',
                'bpdenominator',
                'docname',
                'docrefno',
                'classification',
                'subclassification',
                'hmocode',
                'hmoname',
                'phic_pin',
                'status',
                'queueno',
                'recordedby',
                'recordeddate',
                'laboratorypath',
                'radiologypath'
            ])
            ->orderBy('id', 'desc')
            ->get();

        // Detailed Comment: Bulk pre-load answered doctor questions joining docquestion on questionrefno = docquestionrefno
        // to attach actual question text and patient answers onto every consultation history item without N+1 query overhead.
        $allConsultationRefNos = $history->pluck('consultationrefno')->filter()->toArray();
        $allAnswers = ConsultationAnswerModel::leftJoin('docquestion', 'walkinconsultation_answer.questionrefno', '=', 'docquestion.docquestionrefno')
            ->whereIn('walkinconsultation_answer.pxconsultationrefno', $allConsultationRefNos)
            ->select('walkinconsultation_answer.*', 'docquestion.question')
            ->get()
            ->groupBy('pxconsultationrefno');

        // Detailed Comment: Bulk pre-load permanent medical history records (allergies, immunizations, past medical illnesses,
        // surgical operations, family history, and maintenance medications) across all patient references.
        $allPxRefNos = $history->pluck('pxrefno')->filter()->merge($pxrefnoList)->unique()->toArray();
        $allPinCodes = $history->pluck('pincode')->filter()->merge($pincodeList)->unique()->toArray();

        $allMedHistories = PxMedicalHistoryModel::where(function ($q) use ($allPxRefNos, $allPinCodes) {
            if (!empty($allPxRefNos)) {
                $q->whereIn('pxrefno', $allPxRefNos);
            }
            if (!empty($allPinCodes)) {
                $q->orWhereIn('pincode', $allPinCodes);
            }
        })->get();

        $primaryMedHistory = $allMedHistories->first();

        $history->transform(function ($record) use ($allAnswers, $allMedHistories, $primaryMedHistory) {
            // Detailed Comment: Attach answered doctor questions directly to consultation record
            $record->answers = $allAnswers->get($record->consultationrefno, collect());

            // Detailed Comment: Attach patient permanent medical history directly to consultation record
            $recordMedHistory = $allMedHistories->first(function ($mh) use ($record) {
                return (!empty($record->pxrefno) && $mh->pxrefno === $record->pxrefno) ||
                       (!empty($record->pincode) && $mh->pincode === $record->pincode);
            }) ?? $primaryMedHistory;
            $record->permanent_medhistory = $recordMedHistory;
            // Detailed Comment: Defensive patient resolution. Eager loaded relation $record->patient is checked first;
            // if null (e.g. walk-ins with missing relations), look up pxmasterlist by pxrefno or pincode.
            $patientMaster = $record->patient ?? (
                (!empty($record->pxrefno) || !empty($record->pincode))
                    ? PatientMasterlist::where(function ($q) use ($record) {
                        if (!empty($record->pxrefno)) {
                            $q->where('pxrefno', $record->pxrefno);
                        }
                        if (!empty($record->pincode)) {
                            $q->orWhere('pincode', $record->pincode);
                        }
                    })->first()
                    : null
            );

            // Detailed Comment: Populate patient address from PatientMasterlist components
            // (streetadrs, brgy, muncity, province) or fallback to $patientMaster->address.
            // Eliminates "N/A" placeholders and prevents database schema mismatch crashes.
            if ($patientMaster) {
                $addrParts = array_filter([
                    $patientMaster->streetadrs,
                    $patientMaster->brgy,
                    $patientMaster->muncity,
                    $patientMaster->province
                ]);
                $record->address = !empty($addrParts) ? implode(', ', $addrParts) : ($patientMaster->address ?? 'N/A');

                // Detailed Comment: Backfill demographics from patient masterlist if empty on consultation
                if (empty($record->pincode)) {
                    $record->pincode = $patientMaster->pincode ?: ($patientMaster->phic_pin ?: 'N/A');
                }
                if (empty($record->pxrefno)) {
                    $record->pxrefno = $patientMaster->pxrefno ?: 'N/A';
                }
                if (empty($record->birthday)) {
                    $record->birthday = $patientMaster->birthday;
                }
                if (empty($record->gender)) {
                    $record->gender = $patientMaster->gender ?: 'N/A';
                }
                if (empty($record->mobilenumber)) {
                    $record->mobilenumber = $patientMaster->mobilenumber ?: 'N/A';
                }
                if (empty($record->patientname) && !empty($patientMaster->patientname)) {
                    $record->patientname = $patientMaster->patientname;
                }
                if (empty($record->photo_path) && !empty($patientMaster->photo_path)) {
                    $record->photo_path = $patientMaster->photo_path;
                }
            } else {
                $record->address = 'N/A';
            }

            // Detailed Comment: Provide contract aliases for frontend components (pin, ref, contactno, sex)
            $record->pin = $record->pincode;
            $record->ref = $record->pxrefno;
            $record->contactno = $record->mobilenumber;
            $record->sex = $record->gender;

            // Detailed Comment: Dynamically calculate patient age from birthday if age is null or empty
            if (empty($record->age) && !empty($record->birthday)) {
                try {
                    $record->age = Carbon::parse($record->birthday)->age;
                } catch (\Throwable $e) {
                    $record->age = null;
                }
            }

            // Detailed Comment: Format patient avatar photo URL
            if ($record->photo_path && !str_contains($record->photo_path, 'blank_photo.png')) {
                $filename = basename($record->photo_path);
                $record->photo_path = url('/patient/photo/' . $filename);
                $record->photo_url = url('/patient/photo/' . $filename);
            } else {
                $record->photo_path = url('/images/blank_photo.png');
                $record->photo_url = url('/images/blank_photo.png');
            }

            // Detailed Comment: Generate valid file preview URLs for radiology and laboratory attachments
            // using the secure named route 'preview.file' pointing to the authenticated storage streaming handler.
            $record->radiology_url = (!empty($record->radiologypath) && is_string($record->radiologypath))
                ? route('preview.file', ['path' => $record->radiologypath])
                : null;
            $record->laboratory_url = (!empty($record->laboratorypath) && is_string($record->laboratorypath))
                ? route('preview.file', ['path' => $record->laboratorypath])
                : null;

            // Detailed Comment: Prescriptions (Rx) from stocks_ledger with graceful fallback to legacy DoctorMedicinesModel (pxrxdocuments)
            // Maps dispensed_flag dynamically from dispensed_status / dispensed date for API & UI consistency
            $rxItems = StocksLedgerModel::where('px_consultcode_cn', $record->consultationrefno)
                ->where('item_grouping', 'DRUGS AND MEDS')
                ->select(['prodcode', 'item_dscr', 'qty', 'remarks', 'instructions', 'dispensed_status', 'dispensed'])
                ->get()
                ->map(function ($rx) {
                    $rx->dispensed_flag = ($rx->dispensed_status === 'RELEASED' || !empty($rx->dispensed)) ? 1 : 0;
                    $rx->med_name = $rx->item_dscr;
                    $rx->dosage = $rx->remarks;
                    $rx->quantity = $rx->qty;
                    $rx->is_dispensed = ($rx->dispensed_flag == 1);
                    return $rx;
                });

            if ($rxItems->isEmpty()) {
                $legacyRx = DoctorMedicinesModel::where('consultationrefno', $record->consultationrefno)->get()
                    ->map(function ($docRx) {
                        return (object)[
                            'prodcode' => $docRx->medicinecode ?? 'RX',
                            'item_dscr' => $docRx->medicinename,
                            'med_name' => $docRx->medicinename,
                            'qty' => $docRx->medicinequantity ?: 1,
                            'quantity' => $docRx->medicinequantity ?: 1,
                            'remarks' => $docRx->medicinedosage ?? 'As directed',
                            'dosage' => $docRx->medicinedosage ?? 'As directed',
                            'instructions' => $docRx->medicineduration ?? '',
                            'dispensed_status' => 'PENDING',
                            'dispensed' => null,
                            'dispensed_flag' => 0,
                            'is_dispensed' => false,
                        ];
                    });
                $record->rx_items = $legacyRx;
            } else {
                $record->rx_items = $rxItems;
            }
            $record->prescriptions = $record->rx_items;

            // Detailed Comment: Diagnostic requests from stocks_ledger with graceful fallback to legacy DocRequestsModel & DiagnosticsMasterlistModel
            $diagItems = StocksLedgerModel::where('px_consultcode_cn', $record->consultationrefno)
                ->whereIn('item_grouping', ['DIAGNOSTIC', 'IMAGING'])
                ->select(['prodcode', 'item_dscr', 'item_grouping', 'remarks', 'updated'])
                ->get()
                ->map(function ($diag) {
                    $diag->exam_name = $diag->item_dscr;
                    $diag->category = $diag->item_grouping;
                    $diag->notes = $diag->remarks;
                    $diag->requested_at = $diag->updated;
                    return $diag;
                });

            if ($diagItems->isEmpty()) {
                $pxreq = DocRequestsModel::where(['consultationrefno' => $record->consultationrefno])->get();
                $masterDiagModels = DiagnosticsMasterlistModel::whereIn('diagnosticrefno', $pxreq->pluck('requestrefno')->toArray())
                    ->get()
                    ->keyBy('diagnosticrefno');

                $legacyDiags = $pxreq->map(function ($req) use ($masterDiagModels) {
                    $master = $masterDiagModels->get($req->requestrefno);
                    return (object)[
                        'prodcode' => $req->requestrefno,
                        'item_dscr' => $master->diagnostic_name ?? $req->request_dscr ?? 'Diagnostic Procedure',
                        'exam_name' => $master->diagnostic_name ?? $req->request_dscr ?? 'Diagnostic Procedure',
                        'item_grouping' => $master->diagnostic_catg ?? 'DIAGNOSTIC',
                        'category' => $master->diagnostic_catg ?? 'DIAGNOSTIC',
                        'remarks' => $req->request_dscr ?? '',
                        'notes' => $req->request_dscr ?? '',
                        'requested_at' => $req->created_at ?? $req->recorddate ?? '',
                        'updated' => $req->recorddate ?? '',
                    ];
                });
                $record->diagnostic_items = $legacyDiags;
            } else {
                $record->diagnostic_items = $diagItems;
            }
            $record->diagnostics = $record->diagnostic_items;

            // Detailed Comment: Charges from stocks_ledger including retails and computed totalamt
            $record->charges = StocksLedgerModel::where('px_consultcode_cn', $record->consultationrefno)
                ->select(['id', 'prodcode', 'item_dscr', 'item_grouping', 'qty', 'cost_ave', 'retails', 'totalamt'])
                ->get()
                ->map(function ($c) {
                    $c->item_name = $c->item_dscr;
                    $c->unit_price = $c->cost_ave ?: $c->retails;
                    $c->category = $c->item_grouping;
                    return $c;
                });

            if ($record->charges->isEmpty()) {
                $legacyCharges = DocChargesModel::where('consultationrefno', $record->consultationrefno)->get()
                    ->map(function ($dc) {
                        $qty = $dc->quantity ?: ($dc->qty ?: 1);
                        $unit = $dc->charge ?: ($dc->rate ?: 0);
                        $total = $dc->net_total ?: ($dc->total ?: ($dc->totalamt ?? ($qty * $unit)));
                        $name = $dc->servicename ?: ($dc->chargedesc ?? 'Charge');
                        $code = $dc->servicerefno ?: ($dc->chargecode ?? 'CHARGE');
                        return (object)[
                            'id' => $dc->id,
                            'prodcode' => $code,
                            'item_dscr' => $name,
                            'item_name' => $name,
                            'item_grouping' => 'PROFESSIONAL FEE',
                            'category' => 'PROFESSIONAL FEE',
                            'qty' => $qty,
                            'cost_ave' => $unit,
                            'retails' => $unit,
                            'unit_price' => $unit,
                            'totalamt' => $total,
                        ];
                    });
                if ($legacyCharges->isNotEmpty()) {
                    $record->charges = $legacyCharges;
                }
            }

            $record->charges_total = round((float) $record->charges->sum('totalamt'), 2);

            return $record;
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsFiltered' => $history->count(),
            'recordsTotal' => $history->count(),
            'success' => true,
            'history' => $history,
            'medhistory' => $history,
            'data' => $history,
            'permanent_medhistory' => $primaryMedHistory
        ]);
    }

    // Settlements-related
    public function fetchSettlements(Request $request) {
        $record = SettlementsModel::where(['consultationrefno' => $request->consultationrefno])->first();

        if ($record) {
            return response()->json(['success' => true, 'record' => $record]);
        }

        return response()->json(['success' => false]);
    }

    /**
     * Detailed Comment: Saves or updates consultation billing settlements across Cash, Card (CTA),
     * Detailed Comment: Saves and updates patient consultation billing settlement details in pxsettlements.
     * Computes gross charges and categorized subtotals from stocks_ledger (DRUGS AND MEDS, DIAGNOSTIC,
     * PROFESSIONAL FEE, PROCEDURES, SUPPLIES, VACCINES, IMMUNIZATION), applies PhilHealth (PHIC) and HMO
     * deductions, records cash and card payment channels, generates transaction reference number (TRX...),
     * and logs structured audit events.
     */
    public function saveSettlements(Request $request) {
        $consultRef = $request->sett_consultationrefno ?: $request->consultationrefno;
        $consultation = ConsultationModel::where('consultationrefno', $consultRef)->first();

        // Detailed Comment: Compute categorized charge totals from stocks_ledger for billing auditing and SOA breakdown
        $charges = StocksLedgerModel::where('px_consultcode_cn', $consultRef)->get();
        $totalMeds = (float) $charges->where('item_grouping', 'DRUGS AND MEDS')->sum('totalamt');
        $totalLab = (float) $charges->where('item_grouping', 'DIAGNOSTIC')->sum('totalamt');
        $totalPf = (float) $charges->where('item_grouping', 'PROFESSIONAL FEE')->sum('totalamt');
        $totalProcedures = (float) $charges->where('item_grouping', 'PROCEDURES')->sum('totalamt');
        $totalSupplies = (float) $charges->where('item_grouping', 'SUPPLIES')->sum('totalamt');
        $totalVaccines = (float) $charges->where('item_grouping', 'VACCINES')->sum('totalamt');
        $totalImmunizations = (float) $charges->where('item_grouping', 'IMMUNIZATION')->sum('totalamt');
        $totalOthers = (float) $charges->whereNotIn('item_grouping', ['DRUGS AND MEDS', 'DIAGNOSTIC', 'PROFESSIONAL FEE', 'PROCEDURES', 'SUPPLIES', 'VACCINES', 'IMMUNIZATION'])->sum('totalamt');

        $computedGross = (float) $charges->sum('totalamt');
        $totalGross = $computedGross > 0 ? $computedGross : (float) ($request->total ?? 0);
        // Detailed Comment: Extract deductions including Senior/PWD, PHIC, HMO, and custom discounts
        $lessSrpwd = (float) ($request->less_srpwd ?: 0);
        $srpwdRefNo = $request->srpwd_refno ?: null;
        $lessPhic = (float) ($request->phic ?: ($request->less_phic ?: 0));
        $phicIcdRvs = $request->phic_icd_rvs ?: null;
        $lessHmo = (float) ($request->hmo ?: ($request->less_hmo ?: 0));
        $hmoLoaNo = $request->hmo_loa_no ?: null;
        $lessDiscount = (float) ($request->less_discount ?: ($request->discount ?: 0));
        $discountDescription = $request->discount_description ?: null;
        $isYakap = ($request->boolean('is_philhealth_yakap') || $request->is_philhealth_yakap == '1' || $request->is_philhealth_yakap === true) ? 1 : 0;
        $copay = (float) ($request->copay ?: 0);
        
        // Detailed Comment: Compute net payable after all Senior/PWD, PhilHealth, HMO, and special discounts
        $netPayable = max(0, $totalGross - $lessSrpwd - $lessPhic - $lessHmo - $lessDiscount);

        // Detailed Comment: Resolve HMO code and name from authoritative HMO table "hmo_masterlist"
        $hmoInput = $request->hmo_type;
        $hmoCode = $hmoInput;
        $hmoName = null;
        if (!empty($hmoInput)) {
            $hmoRecord = HMOModel::where('hmocode', $hmoInput)->orWhere('hmoname', $hmoInput)->first();
            if ($hmoRecord) {
                $hmoCode = $hmoRecord->hmocode;
                $hmoName = $hmoRecord->hmoname;
            } else {
                $hmoName = $hmoInput;
            }
        }

        $record = SettlementsModel::updateOrCreate([
            'consultationrefno' => $consultRef
        ], [
            'pincode' => $consultation->pincode ?? $request->pincode ?? null,
            'docrefno' => $consultation->docrefno ?? null,
            'docname' => $consultation->docname ?? null,
            'total_gross' => $totalGross,
            'net_payable' => $netPayable,
            'total_meds' => $totalMeds,
            'total_lab' => $totalLab,
            'total_doctorspf' => $totalPf,
            'total_procedures' => $totalProcedures,
            'total_supplies' => $totalSupplies,
            'total_vaccines' => $totalVaccines,
            'total_immunizations' => $totalImmunizations,
            'total_others' => $totalOthers,
            'less_srpwd' => $lessSrpwd,
            'srpwd_refno' => $srpwdRefNo,
            'less_phic' => $lessPhic,
            'phic_icd_rvs' => $phicIcdRvs,
            'less_hmo' => $lessHmo,
            'hmo_type' => $hmoName ?: $hmoCode,
            'hmocode' => $hmoCode,
            'hmo_loa_no' => $hmoLoaNo,
            'hmoname' => $hmoName ?: $hmoCode,
            'less_discount' => $lessDiscount,
            'discount_description' => $discountDescription,
            'payment_cash' => (float) ($request->cash ?: 0),
            'is_philhealth_yakap' => $isYakap,
            'copay' => $copay,
            'payment_card' => (float) ($request->cta ?: 0),
            'cta_type' => $request->cta_type ?? $request->card_type,
            'created' => Date::now(),
            'createdby' => auth()->guard('secretary')->check()
                ? auth()->guard('secretary')->user()->seclname
                : (auth()->guard('admin')->check() ? auth()->guard('admin')->user()->name : 'System'),
        ]);

        if ($record->wasRecentlyCreated || empty($record->transactionrefno)) {
            $record->transactionrefno = 'TRX' . Date::now()->format('mdYHis');
            $record->save();
        }

        if ($record) {
            // Detailed Comment: Log patient billing settlement save with complete discount audit trail
            Log::info('Consultation settlement saved', [
                'consultationrefno' => $request->sett_consultationrefno,
                'total_gross' => $totalGross,
                'less_srpwd' => $lessSrpwd,
                'srpwd_refno' => $srpwdRefNo,
                'less_phic' => $lessPhic,
                'phic_icd_rvs' => $phicIcdRvs,
                'less_hmo' => $lessHmo,
                'hmocode' => $hmoCode,
                'hmo_loa_no' => $hmoLoaNo,
                'hmoname' => $hmoName,
                'less_discount' => $lessDiscount,
                'discount_description' => $discountDescription,
                'is_philhealth_yakap' => $isYakap,
                'copay' => $copay,
                'net_payable' => $netPayable,
                'transactionrefno' => $record->transactionrefno,
                'recorded_by' => auth()->guard('secretary')->check()
                    ? auth()->guard('secretary')->user()->seclname
                    : (auth()->guard('admin')->check() ? auth()->guard('admin')->user()->name : 'System'),
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    /**
     * Detailed Comment: Fetches available HMO entities from the authoritative "hmo_masterlist" table
     * for the queue settlement modal and secretary consultation consoles.
     * Selects hmocode and hmoname, filters out null or empty names, and orders alphabetically by hmoname.
     * Evaluates clientcode with fallback to session/config and global HMO records.
     */
    public function fetchHmo()
    {
        $clientCode = session()->get('clientcode') ?? config('app.clientcode') ?? env('CLIENT_CODE', '122377');

        $query = HMOModel::select(['hmocode', 'hmoname'])
            ->whereNotNull('hmoname')
            ->where('hmoname', '!=', '')
            ->orderBy('hmoname', 'ASC');

        if ($clientCode) {
            $hmo = (clone $query)->where(function ($q) use ($clientCode) {
                $q->where('dw_clientcode', $clientCode)
                  ->orWhereNull('dw_clientcode')
                  ->orWhere('dw_clientcode', '');
            })->get();
            if ($hmo->isNotEmpty()) {
                return response()->json(['hmo' => $hmo]);
            }
        }

        $hmo = $query->get();
        return response()->json(['hmo' => $hmo]);
    }

    /**
     * Detailed Comment: Fetches payment and settlement history for a patient based on consultation history.
     * Resolves all past consultations for this patient by pincode or pxrefno, joins with pxsettlements,
     * and returns historical payment records, deductions, and payment status.
     */
    public function fetchPatientPaymentHistory(Request $request)
    {
        $pincode = $request->input('pincode');
        $pxrefno = $request->input('pxrefno');
        $consultationrefno = $request->input('consultationrefno');

        $query = SettlementsModel::query();

        if ($pxrefno || $pincode) {
            $consulRefs = ConsultationModel::where(function ($q) use ($pxrefno, $pincode) {
                if ($pxrefno) $q->where('pxrefno', $pxrefno);
                if ($pincode) $q->orWhere('pincode', $pincode);
            })->pluck('consultationrefno')->filter()->toArray();

            $query->where(function ($q) use ($consulRefs, $pincode) {
                if (!empty($consulRefs)) {
                    $q->whereIn('consultationrefno', $consulRefs);
                }
                if ($pincode) {
                    $q->orWhere('pincode', $pincode);
                }
            });
        } elseif ($consultationrefno) {
            $query->where('consultationrefno', $consultationrefno);
        } else {
            return response()->json(['success' => true, 'payments' => []]);
        }

        $payments = $query->orderBy('id', 'desc')->get();

        $consultations = ConsultationModel::whereIn('consultationrefno', $payments->pluck('consultationrefno')->filter()->toArray())
            ->pluck('consultation_date', 'consultationrefno');

        $payments->transform(function ($payment) use ($consultations) {
            $rawDate = $consultations[$payment->consultationrefno] ?? $payment->created;
            $payment->consultation_date = $rawDate ? date('Y-m-d', strtotime($rawDate)) : 'N/A';
            $totalPaid = (float)($payment->payment_cash ?? 0) + (float)($payment->payment_card ?? 0);
            $net = (float)($payment->net_payable ?? $payment->total_gross ?? 0);
            $payment->payment_status = ($totalPaid >= $net && $net > 0) ? 'PAID' : (($totalPaid > 0) ? 'PARTIAL' : 'UNPAID');
            return $payment;
        });

        return response()->json([
            'success' => true,
            'payments' => $payments
        ]);
    }

    /**
     * Detailed Comment: Fetches permanent medical history records (allergies, injections/immunizations,
     * past medical history, surgical history, family history, and maintenance medications) for a patient.
     * Cross-references pxrefno and pincode across masterlist and active consultation.
     */
    public function fetchPatientMedicalHistory(Request $request)
    {
        $pxrefno = $request->input('pxrefno');
        $pincode = $request->input('pincode');
        $consultationrefno = $request->input('consultationrefno');

        // Detailed Comment: Defensive sanitization of string placeholders 'undefined' and 'null'
        if ($pxrefno === 'undefined' || $pxrefno === 'null') $pxrefno = null;
        if ($pincode === 'undefined' || $pincode === 'null') $pincode = null;
        if ($consultationrefno === 'undefined' || $consultationrefno === 'null') $consultationrefno = null;

        if (empty($pxrefno) && !empty($consultationrefno)) {
            $consult = ConsultationModel::where('consultationrefno', $consultationrefno)->first();
            if ($consult) {
                $pxrefno = $consult->pxrefno;
                if (empty($pincode)) $pincode = $consult->pincode;

                // Fallback to patient masterlist by name and birthday if pxrefno is missing on consultation
                if (empty($pxrefno) && !empty($consult->patientname) && !empty($consult->birthday)) {
                    $pMaster = PatientMasterlist::where('patientname', $consult->patientname)
                        ->where('birthday', $consult->birthday)
                        ->first();
                    if ($pMaster) {
                        $pxrefno = $pMaster->pxrefno;
                        if (empty($pincode)) $pincode = $pMaster->pincode;
                    }
                }
            }
        }

        if (empty($pxrefno) && !empty($pincode)) {
            $pMaster = PatientMasterlist::where('pincode', $pincode)->first();
            if ($pMaster) {
                $pxrefno = $pMaster->pxrefno;
            }
        }

        $medHistory = null;
        if (!empty($pxrefno) || !empty($pincode)) {
            $medHistory = PxMedicalHistoryModel::where(function ($q) use ($pxrefno, $pincode) {
                if (!empty($pxrefno)) $q->where('pxrefno', $pxrefno);
                if (!empty($pincode)) $q->orWhere('pincode', $pincode);
            })->first();
        }

        return response()->json([
            'success' => true,
            'pxrefno' => $pxrefno,
            'pincode' => $pincode,
            'data' => $medHistory,
            'permanent_medhistory' => $medHistory
        ]);
    }

    /**
     * Detailed Comment: Creates or updates permanent medical history records for a patient.
     * Persists allergies, injections/immunizations, past medical illnesses, surgical operations,
     * family medical history, and maintenance medications with audit logging of recordedby/updatedby.
     */
    public function savePatientMedicalHistory(Request $request)
    {
        $pxrefno = $request->input('pxrefno');
        $pincode = $request->input('pincode');
        $consultationrefno = $request->input('consultationrefno');

        // Detailed Comment: Defensive sanitization of string placeholders 'undefined' and 'null'
        if ($pxrefno === 'undefined' || $pxrefno === 'null') $pxrefno = null;
        if ($pincode === 'undefined' || $pincode === 'null') $pincode = null;
        if ($consultationrefno === 'undefined' || $consultationrefno === 'null') $consultationrefno = null;

        // Resolve missing pxrefno from consultation or masterlist
        $consult = null;
        if (empty($pxrefno) && !empty($consultationrefno)) {
            $consult = ConsultationModel::where('consultationrefno', $consultationrefno)->first();
            if ($consult) {
                $pxrefno = $consult->pxrefno;
                if (empty($pincode)) $pincode = $consult->pincode;

                if (empty($pxrefno) && !empty($consult->patientname) && !empty($consult->birthday)) {
                    $pMaster = PatientMasterlist::where('patientname', $consult->patientname)
                        ->where('birthday', $consult->birthday)
                        ->first();
                    if ($pMaster) {
                        $pxrefno = $pMaster->pxrefno;
                        if (empty($pincode)) $pincode = $pMaster->pincode;
                    }
                }
            }
        }

        if (empty($pxrefno) && !empty($pincode)) {
            $pMaster = PatientMasterlist::where('pincode', $pincode)->first();
            if ($pMaster) {
                $pxrefno = $pMaster->pxrefno;
            }
        }

        if (empty($pxrefno) && empty($pincode)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot save medical history: Patient reference number (pxrefno) or PIN code is required.'
            ], 422);
        }

        // Determine current user across secretary, doctor, or admin guards
        $userDisplay = 'System';
        if (auth()->guard('secretary')->check()) {
            $sec = auth()->guard('secretary')->user();
            $userDisplay = trim(($sec->secfname ?? '') . ' ' . ($sec->seclname ?? '')) ?: ($sec->secname ?? 'Secretary');
        } elseif (auth()->guard('doctor')->check()) {
            $doc = auth()->guard('doctor')->user();
            $userDisplay = 'Dr. ' . ($doc->docname ?? 'Physician');
        } elseif (auth()->guard('admin')->check()) {
            $userDisplay = auth()->guard('admin')->user()->name ?? 'Administrator';
        }

        $existing = PxMedicalHistoryModel::where(function ($q) use ($pxrefno, $pincode) {
            if (!empty($pxrefno)) $q->where('pxrefno', $pxrefno);
            if (!empty($pincode)) $q->orWhere('pincode', $pincode);
        })->first();

        $data = [
            'pxrefno' => $pxrefno ?: ($existing->pxrefno ?? null),
            'pincode' => $pincode ?: ($existing->pincode ?? null),
            'allergies' => $request->input('allergies'),
            'injections_immunization' => $request->input('injections_immunization'),
            'past_medical_history' => $request->input('past_medical_history'),
            'surgical_history' => $request->input('surgical_history'),
            'family_history' => $request->input('family_history'),
            'maintenance_medications' => $request->input('maintenance_medications'),
            'notes' => $request->input('notes'),
            'updatedby' => $userDisplay,
        ];

        if (!$existing) {
            $data['recordedby'] = $userDisplay;
            $record = PxMedicalHistoryModel::create($data);
        } else {
            $existing->update($data);
            $record = $existing;
        }

        Log::info('Saved patient permanent medical history', [
            'pxrefno' => $pxrefno,
            'pincode' => $pincode,
            'updatedby' => $userDisplay,
            'has_allergies' => !empty($request->input('allergies')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient medical history successfully saved.',
            'data' => $record
        ]);
    }

    /**
     * Detailed Comment: Fetches PhilHealth Yakap Medical & Surgical History for active patient in Secretary Console.
     * Searches across dd_profile_1, dd_profile_medhist, dd_profile_mhspecific, and dd_profile_surghist
     * resolving links via px_pin, pincode, pxrefno, or consultation case number.
     */
    public function fetchPatientYakapMedicalHistory(Request $request)
    {
        $pxrefno = $request->input('pxrefno');
        $pincode = $request->input('pincode');
        $caseno = $request->input('caseno');
        $consultationrefno = $request->input('consultationrefno');

        if ($pxrefno === 'undefined' || $pxrefno === 'null') $pxrefno = null;
        if ($pincode === 'undefined' || $pincode === 'null') $pincode = null;
        if ($caseno === 'undefined' || $caseno === 'null') $caseno = null;
        if ($consultationrefno === 'undefined' || $consultationrefno === 'null') $consultationrefno = null;

        // Detailed Comment: Resolve missing identifiers from ConsultationModel or PatientMasterlist
        if (!empty($consultationrefno)) {
            $consult = ConsultationModel::where('consultationrefno', $consultationrefno)->first();
            if ($consult) {
                if (empty($pxrefno)) $pxrefno = $consult->pxrefno;
                if (empty($pincode)) $pincode = $consult->pincode ?: $consult->phic_pin;
                if (empty($caseno)) $caseno = $consult->caseno ?: $consult->en_caseno;
            }
        }

        if (empty($pxrefno) && !empty($pincode)) {
            $pMaster = PatientMasterlist::where('pincode', $pincode)->first();
            if ($pMaster) {
                $pxrefno = $pMaster->pxrefno;
            }
        } elseif (!empty($pxrefno) && empty($pincode)) {
            $pMaster = PatientMasterlist::where('pxrefno', $pxrefno)->first();
            if ($pMaster) {
                $pincode = $pMaster->pincode;
            }
        }

        // Detailed Comment: Locate existing Yakap Profile in dd_profile_1
        $profile = null;
        if (!empty($caseno)) {
            $profile = ProfileModel::where('en_CaseNo', $caseno)->first();
        }
        if (!$profile && !empty($pincode)) {
            $profile = ProfileModel::where('dPatientPin', $pincode)
                ->orWhere('px_pin', $pincode)
                ->first();
        }
        if (!$profile && !empty($pxrefno)) {
            $profile = ProfileModel::where('px_pin', $pxrefno)->first();
        }

        $transNo = $profile ? $profile->dTransNo : null;
        $profileCaseNo = $profile ? $profile->en_CaseNo : $caseno;
        $pinToMatch = $profile ? ($profile->px_pin ?: $profile->dPatientPin) : ($pincode ?: $pxrefno);

        // Detailed Comment: Query medical conditions and specific descriptions
        $medHistQuery = ProfileMedHistModel::query();
        if ($transNo) {
            $medHistQuery->where('p_TransNo', $transNo);
        } elseif (!empty($pinToMatch)) {
            $medHistQuery->where('px_pin', $pinToMatch);
        } else {
            $medHistQuery->whereRaw('1 = 0');
        }
        $medHist = $medHistQuery->get();

        $mhSpecificQuery = ProfileMhSpecificModel::query();
        if ($transNo) {
            $mhSpecificQuery->where('p_TransNo', $transNo);
        } elseif (!empty($pinToMatch)) {
            $mhSpecificQuery->where('px_pin', $pinToMatch);
        } else {
            $mhSpecificQuery->whereRaw('1 = 0');
        }
        $mhSpecific = $mhSpecificQuery->get();

        // Detailed Comment: Query past surgical procedures
        $surgHistQuery = ProfileSurgHistModel::query();
        if ($transNo) {
            $surgHistQuery->where('p_TransNo', $transNo);
        } elseif (!empty($pinToMatch)) {
            $surgHistQuery->where('px_pin', $pinToMatch);
        } else {
            $surgHistQuery->whereRaw('1 = 0');
        }
        $surgHist = $surgHistQuery->get();

        Log::info('Fetched Yakap Medical & Surgical History for patient', [
            'pxrefno' => $pxrefno,
            'pincode' => $pincode,
            'caseno' => $caseno,
            'transNo' => $transNo,
            'conditions_count' => $medHist->count(),
            'surgeries_count' => $surgHist->count(),
        ]);

        return response()->json([
            'success' => true,
            'profile' => $profile,
            'transNo' => $transNo,
            'medHist' => $medHist,
            'mhSpecific' => $mhSpecific,
            'surgHist' => $surgHist,
        ]);
    }

    /**
     * Detailed Comment: Persists PhilHealth Yakap Medical & Surgical History from Secretary Console.
     * Updates dd_profile_medhist, dd_profile_mhspecific, and dd_profile_surghist, creating a profile
     * header record in dd_profile_1 if one does not already exist.
     */
    public function savePatientYakapMedicalHistory(Request $request)
    {
        $pxrefno = $request->input('pxrefno');
        $pincode = $request->input('pincode');
        $caseno = $request->input('caseno');
        $consultationrefno = $request->input('consultationrefno');
        $patientname = $request->input('patientname');
        $transNo = $request->input('transNo');

        if ($pxrefno === 'undefined' || $pxrefno === 'null') $pxrefno = null;
        if ($pincode === 'undefined' || $pincode === 'null') $pincode = null;
        if ($caseno === 'undefined' || $caseno === 'null') $caseno = null;
        if ($consultationrefno === 'undefined' || $consultationrefno === 'null') $consultationrefno = null;
        if ($transNo === 'undefined' || $transNo === 'null') $transNo = null;

        // Detailed Comment: Resolve missing patient identifiers
        if (!empty($consultationrefno)) {
            $consult = ConsultationModel::where('consultationrefno', $consultationrefno)->first();
            if ($consult) {
                if (empty($pxrefno)) $pxrefno = $consult->pxrefno;
                if (empty($pincode)) $pincode = $consult->pincode ?: $consult->phic_pin;
                if (empty($caseno)) $caseno = $consult->caseno ?: $consult->en_caseno;
                if (empty($patientname)) $patientname = $consult->patientname;
            }
        }

        if (empty($pxrefno) && !empty($pincode)) {
            $pMaster = PatientMasterlist::where('pincode', $pincode)->first();
            if ($pMaster) {
                $pxrefno = $pMaster->pxrefno;
                if (empty($patientname)) $patientname = $pMaster->patientname;
            }
        }

        if (empty($pxrefno) && empty($pincode)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot save Yakap medical history: Patient reference number or PIN is required.'
            ], 422);
        }

        // Determine current logged in user display
        $userDisplay = 'System';
        if (auth()->guard('secretary')->check()) {
            $sec = auth()->guard('secretary')->user();
            $userDisplay = trim(($sec->secfname ?? '') . ' ' . ($sec->seclname ?? '')) ?: ($sec->secname ?? 'Secretary');
        } elseif (auth()->guard('admin')->check()) {
            $userDisplay = auth()->guard('admin')->user()->name ?? 'Administrator';
        }

        /** @var ClientService $clientService */
        $clientService = app(ClientService::class);
        $clientCode = $clientService->getClientCode() ?: '122377';

        try {
            DB::beginTransaction();

            // Detailed Comment: Locate or generate Yakap profile record
            $profile = null;
            if (!empty($transNo)) {
                $profile = ProfileModel::where('dTransNo', $transNo)->first();
            }
            if (!$profile && !empty($caseno)) {
                $profile = ProfileModel::where('en_CaseNo', $caseno)->first();
            }
            if (!$profile && !empty($pincode)) {
                $profile = ProfileModel::where('dPatientPin', $pincode)
                    ->orWhere('px_pin', $pincode)
                    ->first();
            }
            if (!$profile && !empty($pxrefno)) {
                $profile = ProfileModel::where('px_pin', $pxrefno)->first();
            }

            if ($profile) {
                $transNo = $profile->dTransNo;
                $profileCaseNo = $profile->en_CaseNo ?: ($caseno ?: 'C' . now()->format('YmdHis'));
                $pinCodeToUse = $profile->px_pin ?: ($pincode ?: $pxrefno);
            } else {
                // Generate a unique Yakap profile transaction number
                $accreNo = 'H10037034';
                try {
                    $accreNo = $clientService->getHciAccreditationNumber() ?: 'H10037034';
                } catch (\Exception $e) {
                    $accreNo = 'H10037034';
                }
                $transNo = 'P' . $accreNo . now()->format('Ym') . rand(10000, 99999);
                $profileCaseNo = $caseno ?: ('C' . now()->format('YmdHis'));
                $pinCodeToUse = $pincode ?: $pxrefno;

                $profile = ProfileModel::create([
                    'dw_clientcode' => $clientCode,
                    'dTransNo' => $transNo,
                    'en_CaseNo' => $profileCaseNo,
                    'px_pin' => $pinCodeToUse,
                    'dProfDate' => now()->toDateString(),
                    'dPatientPin' => $pincode ?: $pinCodeToUse,
                    'patientname' => $patientname ?: 'Walk-in Patient',
                    'dPatientType' => 'MM',
                    'dPatientAge' => '',
                    'dMemPin' => $pincode ?: $pinCodeToUse,
                    'dEffyear' => now()->format('Y'),
                    'dATC' => '',
                    'dIsWalkedIn' => 'Y',
                    'dTransDate' => now()->toDateString(),
                    'dReportStatus' => 'U',
                ]);
            }

            // Detailed Comment: Process and persist Past Medical Conditions
            $chkMedHistDiseases = $request->input('chkMedHistDiseases', []);
            $medhistToSave = [];
            $mhspecificToSave = [];

            if (is_array($chkMedHistDiseases)) {
                foreach ($chkMedHistDiseases as $diseaseCode => $condition) {
                    if (isset($condition['selected']) && !empty($condition['selected'])) {
                        $medhistToSave[] = [
                            'dw_clientcode' => $clientCode,
                            'px_pin' => $pinCodeToUse,
                            'p_TransNo' => $transNo,
                            'en_caseno' => $profileCaseNo,
                            'dMdiseaseCode' => (string)$diseaseCode,
                            'dReportStatus' => 'U',
                            'dDeficiencyRemarks' => '',
                            'createdby' => $userDisplay,
                            'created' => now(),
                            'updatedby' => $userDisplay,
                            'updated' => now(),
                        ];

                        if (isset($condition['specify']) && trim($condition['specify']) !== '') {
                            $mhspecificToSave[] = [
                                'dw_clientcode' => $clientCode,
                                'px_pin' => $pinCodeToUse,
                                'en_caseno' => $profileCaseNo,
                                'p_TransNo' => $transNo,
                                'dMdiseaseCode' => (string)$diseaseCode,
                                'dSpecificDesc' => trim($condition['specify']),
                                'dReportStatus' => 'U',
                                'dDeficiencyRemarks' => '',
                                'created' => now(),
                                'createdby' => $userDisplay,
                                'updated' => now(),
                                'updatedby' => $userDisplay,
                            ];
                        }
                    }
                }
            }

            // Delete previous medical conditions for this profile transaction number and insert fresh ones
            ProfileMedHistModel::where('p_TransNo', $transNo)->delete();
            ProfileMhSpecificModel::where('p_TransNo', $transNo)->delete();

            if (!empty($medhistToSave)) {
                ProfileMedHistModel::insert($medhistToSave);
            }
            if (!empty($mhspecificToSave)) {
                ProfileMhSpecificModel::insert($mhspecificToSave);
            }

            // Detailed Comment: Process and persist Past Surgical Procedures
            $surgicalHistory = $request->input('surgicalHistory', []);
            $surgHistToSave = [];

            if (is_array($surgicalHistory)) {
                foreach ($surgicalHistory as $surg) {
                    $op = isset($surg['operation']) ? trim($surg['operation']) : '';
                    $date = isset($surg['date']) ? trim($surg['date']) : '';
                    if (!empty($op)) {
                        $surgHistToSave[] = [
                            'dw_clientcode' => $clientCode,
                            'px_pin' => $pinCodeToUse,
                            'p_TransNo' => $transNo,
                            'en_caseno' => $profileCaseNo,
                            'dSurgDesc' => $op,
                            'dSurgDate' => !empty($date) ? $date : null,
                            'dReportStatus' => 'U',
                            'dDeficiencyRemarks' => '',
                            'createdby' => $userDisplay,
                            'created' => now(),
                            'updatedby' => $userDisplay,
                            'updated' => now(),
                        ];
                    }
                }
            }

            // Delete previous surgical records for this profile and insert updated ones
            ProfileSurgHistModel::where('p_TransNo', $transNo)->delete();
            if (!empty($surgHistToSave)) {
                ProfileSurgHistModel::insert($surgHistToSave);
            }

            DB::commit();

            Log::info('Successfully saved Yakap Medical & Surgical History', [
                'transNo' => $transNo,
                'px_pin' => $pinCodeToUse,
                'conditions_saved' => count($medhistToSave),
                'surgeries_saved' => count($surgHistToSave),
                'saved_by' => $userDisplay,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Yakap Medical History and Profiling successfully saved.',
                'transNo' => $transNo,
                'profile' => $profile,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Yakap Medical History', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving Yakap Medical History: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detailed Comment: Computes aggregated daily income financial report for all patients in queue.
     * Calculates total gross billing, PhilHealth deductions, HMO coverage, Senior/PWD discounts,
     * net payable, cash collected, card/CTA, PhilHealth Yakap/Co-Pay, total collected, and remaining balance.
     */
    public function fetchQueueFinancialSummary(Request $request)
    {
        $queueDate = $request->input('queuedate') ?: $request->input('date') ?: now()->toDateString();
        $docrefno = $request->input('docrefno');

        $query = ConsultationModel::whereDate('consultation_date', $queueDate);
        if (!empty($docrefno) && $docrefno !== 'null' && $docrefno !== 'all') {
            $query->where('docrefno', $docrefno);
        }

        $consultations = $query->orderBy('queueno', 'asc')->orderBy('id', 'asc')->get();
        $consultationRefNos = $consultations->pluck('consultationrefno')->filter()->toArray();

        $settlements = SettlementsModel::whereIn('consultationrefno', $consultationRefNos)
            ->get()
            ->keyBy('consultationrefno');

        $totalGross = 0;
        $totalPhic = 0;
        $totalHmo = 0;
        $totalSenior = 0;
        $totalNet = 0;
        $totalCash = 0;
        $totalCard = 0;
        $totalCopay = 0;
        $totalPaid = 0;
        $totalBalance = 0;

        $items = [];

        foreach ($consultations as $consult) {
            $ref = $consult->consultationrefno;
            $settle = $settlements->get($ref);

            $gross = (float)($settle->total_gross ?? $settle->total_amount ?? 0);
            $phic = (float)($settle->less_phic ?? 0);
            $hmo = (float)($settle->less_hmo ?? 0);
            $senior = (float)($settle->less_srpwd ?? $settle->discount_senior ?? 0);
            $net = (float)($settle->net_payable ?? $settle->net_billing ?? 0);
            $cash = (float)($settle->payment_cash ?? 0);
            $card = (float)($settle->payment_card ?? 0);
            $copay = (float)($settle->copay ?? 0);
            $paid = $cash + $card + $copay;
            $balance = max(0, $net - $paid);

            $totalGross += $gross;
            $totalPhic += $phic;
            $totalHmo += $hmo;
            $totalSenior += $senior;
            $totalNet += $net;
            $totalCash += $cash;
            $totalCard += $card;
            $totalCopay += $copay;
            $totalPaid += $paid;
            $totalBalance += $balance;

            $status = 'UNPAID';
            if ($settle) {
                if ($paid >= $net && $net > 0) {
                    $status = 'PAID';
                } elseif ($paid > 0) {
                    $status = 'PARTIAL';
                }
            }

            $items[] = [
                'queueno' => $consult->queueno ?: '--',
                'consultationrefno' => $consult->consultationrefno,
                'patientname' => $consult->patientname,
                'docname' => $consult->docname,
                'gross' => $gross,
                'phic' => $phic,
                'hmo' => $hmo,
                'senior' => $senior,
                'net' => $net,
                'cash' => $cash,
                'card' => $card,
                'copay' => $copay,
                'paid' => $paid,
                'balance' => $balance,
                'status' => $status,
                'is_philhealth_yakap' => (bool)($settle->is_philhealth_yakap ?? false),
                'hmo_loa_no' => $settle->hmo_loa_no ?? '',
            ];
        }

        return response()->json([
            'success' => true,
            'queuedate' => $queueDate,
            'date' => $queueDate,
            'total_patients' => $consultations->count(),
            'settled_patients' => $settlements->count(),
            'summary' => [
                'total_patients' => $consultations->count(),
                'total_gross' => round($totalGross, 2),
                'gross_total' => round($totalGross, 2),
                'total_phic' => round($totalPhic, 2),
                'total_hmo' => round($totalHmo, 2),
                'total_senior' => round($totalSenior, 2),
                'total_srpwd' => round($totalSenior, 2),
                'total_net' => round($totalNet, 2),
                'total_cash' => round($totalCash, 2),
                'total_card' => round($totalCard, 2),
                'total_copay' => round($totalCopay, 2),
                'total_paid' => round($totalPaid, 2),
                'total_balance' => round($totalBalance, 2),
            ],
            'items' => $items,
        ]);
    }

    /**
     * Detailed Comment: Streams PDF printable report for the daily queue financial summary.
     * Formats consolidated billing, PhilHealth/HMO deductions, cash/card/copay collections,
     * and itemized patient billing slips for clinic administration and financial audit.
     */
    public function printFinancialReport(Request $request)
    {
        $queueDate = $request->input('queuedate') ?: $request->input('date') ?: now()->toDateString();
        $docrefno = $request->input('docrefno');

        $query = ConsultationModel::whereDate('consultation_date', $queueDate);
        $doctorName = 'All Attending Doctors';
        if (!empty($docrefno) && $docrefno !== 'null' && $docrefno !== 'all') {
            $query->where('docrefno', $docrefno);
            $doc = DoctorsProfileModel::where('docrefno', $docrefno)->first();
            if ($doc) {
                $doctorName = 'Dr. ' . $doc->docname;
            }
        }

        $consultations = $query->orderBy('queueno', 'asc')->orderBy('id', 'asc')->get();
        $consultationRefNos = $consultations->pluck('consultationrefno')->filter()->toArray();

        $settlements = SettlementsModel::whereIn('consultationrefno', $consultationRefNos)
            ->get()
            ->keyBy('consultationrefno');

        $totalGross = 0;
        $totalPhic = 0;
        $totalHmo = 0;
        $totalSenior = 0;
        $totalNet = 0;
        $totalCash = 0;
        $totalCard = 0;
        $totalCopay = 0;
        $totalPaid = 0;
        $totalBalance = 0;

        $items = [];

        foreach ($consultations as $consult) {
            $ref = $consult->consultationrefno;
            $settle = $settlements->get($ref);

            $gross = (float)($settle->total_gross ?? 0);
            $phic = (float)($settle->less_phic ?? 0);
            $hmo = (float)($settle->less_hmo ?? 0);
            $senior = (float)($settle->discount_senior ?? 0);
            $net = (float)($settle->net_payable ?? 0);
            $cash = (float)($settle->payment_cash ?? 0);
            $card = (float)($settle->payment_card ?? 0);
            $copay = (float)($settle->copay ?? 0);
            $paid = $cash + $card + $copay;
            $balance = max(0, $net - $paid);

            $totalGross += $gross;
            $totalPhic += $phic;
            $totalHmo += $hmo;
            $totalSenior += $senior;
            $totalNet += $net;
            $totalCash += $cash;
            $totalCard += $card;
            $totalCopay += $copay;
            $totalPaid += $paid;
            $totalBalance += $balance;

            $status = 'UNPAID';
            if ($settle) {
                if ($paid >= $net && $net > 0) {
                    $status = 'PAID';
                } elseif ($paid > 0) {
                    $status = 'PARTIAL';
                }
            }

            $items[] = (object)[
                'queueno' => $consult->queueno ?: '--',
                'consultationrefno' => $consult->consultationrefno,
                'patientname' => $consult->patientname,
                'docname' => $consult->docname,
                'gross' => $gross,
                'phic' => $phic,
                'hmo' => $hmo,
                'senior' => $senior,
                'net' => $net,
                'cash' => $cash,
                'card' => $card,
                'copay' => $copay,
                'paid' => $paid,
                'balance' => $balance,
                'status' => $status,
                'is_philhealth_yakap' => (bool)($settle->is_philhealth_yakap ?? false),
                'hmo_loa_no' => $settle->hmo_loa_no ?? '',
            ];
        }

        $summary = (object)[
            'total_gross' => $totalGross,
            'total_phic' => $totalPhic,
            'total_hmo' => $totalHmo,
            'total_senior' => $totalSenior,
            'total_net' => $totalNet,
            'total_cash' => $totalCash,
            'total_card' => $totalCard,
            'total_copay' => $totalCopay,
            'total_paid' => $totalPaid,
            'total_balance' => $totalBalance,
            'total_patients' => $consultations->count(),
        ];

        $profile = KayakapProfileModel::first() ?? (object)[
            'HOSP_NAME' => config('app.name', 'KayakapMD Clinic'),
            'HOSP_ADDBRGY' => ''
        ];

        $generatedBy = 'Secretary';
        if (auth()->guard('secretary')->check()) {
            $sec = auth()->guard('secretary')->user();
            $generatedBy = trim(($sec->secfname ?? '') . ' ' . ($sec->seclname ?? '')) ?: 'Secretary';
        }

        try {
            return Pdf::loadView('printables.financial_report_print', compact('summary', 'items', 'queueDate', 'doctorName', 'profile', 'generatedBy'))
                ->setPaper('A4', 'landscape')
                ->stream('daily_financial_report_' . $queueDate . '.pdf');
        } catch (\Throwable $e) {
            Log::error('Failed to generate daily financial report PDF', [
                'queuedate' => $queueDate,
                'error' => $e->getMessage(),
            ]);

            return response('Error generating financial report PDF: ' . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }
}

