<?php

namespace App\Http\Controllers;

use App\Models\HMOModel;
use App\Models\SecretaryModel;
use Date;
use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ConsultationModel;
use App\Models\DoctorQuestionsModel;
use App\Models\DoctorsProfileModel;
use App\Models\ScheduleModel;
use App\Models\SecretaryDoctorsModel;
use App\Models\SettlementsModel;
use App\Models\ServicesGroupManagementModel;
use App\Models\ServicesManagementModel;
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
        $secUser = auth()->guard('secretary')->user();
        $assigned_doctors = $secUser ? SecretaryDoctorsModel::where([
            'secrefno' => $secUser->secrefno
        ])->pluck('docrefno') : collect();

        $doctors = DoctorsProfileModel::select(['docrefno', 'docname'])
            ->whereIn('docrefno', $assigned_doctors)
            ->get();

        $secretaries = SecretaryModel::select(['secrefno', 'seclname', 'secfname'])->get();

        // Detailed Comment: Structured logging when secretary queue page is rendered
        Log::info('Secretary queue page rendered', [
            'secretary_id' => $secUser ? $secUser->id : null,
            'secrefno' => $secUser ? $secUser->secrefno : null,
            'assigned_doctors_count' => $doctors->count()
        ]);

        return view('pages.secretary.queue', [
            'doctors' => $doctors,
            'secretaries' => $secretaries
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
        $assigned_doctors = SecretaryDoctorsModel::where(['secrefno' => auth()->guard('secretary')->user()->secrefno])->pluck('docrefno');
        $doctors = DoctorsProfileModel::select(['docrefno', 'docname'])->whereIn('docrefno', $assigned_doctors)->get();

        return response()->json(['success' => true, 'doctors' => $doctors]);
    }

    // Question-related
    public function fetchQuestions(Request $request)
    {
        $docquestions = DoctorQuestionsModel::select(['docquestionrefno', 'question'])->where('docrefno', $request->docrefno)->get();

        return response()->json(['docquestions' => $docquestions]);
    }

    public function createQuestion(Request $request)
    {
        // Secretary pero admin ni na user kay gipamove diri from secretary
        $secretary = auth()->guard('admin')->user();
        $doctor = DoctorsProfileModel::where(['docrefno' => $request->docrefno])->first();

        DoctorQuestionsModel::create([
            'docquestionrefno' => Date::now()->format('mdYHis') . "QT",
            "secrefno" => $secretary->adminrefno,
            "question" => $request->question,
            "docrefno" => $request->docrefno,
            "docname" => $doctor->fname . ' ' . $doctor->mname . ' ' . $doctor->lname,
            "recordeddate" => Date::now(),
            "recordedby" => $secretary->username //$secretary->seclname . ', ' . $secretary->secfname . ' ' . $secretary->secmname . ' ' . $secretary->secsuffix,
        ]);

        return response()->json(['success' => true]);
    }

    // Detailed Comment: Schedule-related operations for physicians
    public function fetchSchedules(Request $request)
    {
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

        return response()->json(['success' => true, 'schedules' => $schedules]);
    }

    public function fetchSchedulesSpecific(Request $request)
    {
        $consulDate = $request->consuldate ? new DateTime($request->consuldate) : new DateTime();
        $weekday = $consulDate->format('l');

        $schedules = ScheduleModel::where([
            'docrefno' => $request->docrefno,
            'day' => $weekday
        ])->orderBy('start', 'ASC')->get();

        return response()->json(['success' => true, 'schedules' => $schedules]);
    }

    // To get specific schedule by schedrefno
    public function fetchScheduleByRef(Request $request) {
        $sched = ScheduleModel::where(['schedrefno' => $request->schedrefno])->first();

        return response()->json(['success' => true, 'sched' => $sched]);
    }

    public function editSchedule(Request $request) {
        $request->validate([
            'schedrefno' => 'required|string',
            'day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start' => 'required',
            'end' => 'required'
        ]);

        ScheduleModel::where(['schedrefno' => $request->schedrefno])
            ->update([
                'day' => $request->day,
                'start' => $request->start,
                'end' => $request->end,
            ]);

        Log::info('Doctor schedule updated', [
            'schedrefno' => $request->schedrefno,
            'day' => $request->day,
            'start' => $request->start,
            'end' => $request->end
        ]);

        return response()->json(['success' => true]);
    }

    public function createSchedule(Request $request)
    {
        // Detailed Comment: Support both frontend modal parameter keys (day, stime, etime) and API keys (schedule_day, sched_from, sched_to)
        $day = $request->input('schedule_day') ?: $request->input('day');
        $from = $request->input('sched_from') ?: $request->input('stime');
        $to = $request->input('sched_to') ?: $request->input('etime');

        $request->merge([
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

        $facilityClientCode = config('app.client_code', env('CLIENT_CODE', '122377'));
        $schedrefno = Date::now()->format('mdYHis') . 'SCHED' . rand(10, 99);

        $schedule = ScheduleModel::create([
            'dw_clientcode' => $facilityClientCode,
            'schedrefno' => $schedrefno,
            'docrefno' => $request->docrefno,
            'day' => $request->schedule_day,
            'start' => $request->sched_from,
            'end' => $request->sched_to,
        ]);

        // Detailed Comment: Structured logging for schedule creation
        Log::info('Doctor schedule created', [
            'schedrefno' => $schedrefno,
            'docrefno' => $request->docrefno,
            'day' => $request->schedule_day,
            'start' => $request->sched_from,
            'end' => $request->sched_to
        ]);

        return response()->json(['success' => true, 'schedule' => $schedule]);
    }

    public function deleteSchedule(Request $request) {
        $request->validate([
            'schedrefno' => 'required|string'
        ]);

        ScheduleModel::where('schedrefno', $request->schedrefno)->delete();

        Log::info('Doctor schedule deleted', ['schedrefno' => $request->schedrefno]);

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
     * Detailed Comment: Fetches medical history for a patient in secretary queue.
     * Supports multi-key lookup (pincode, pxrefno, or consultationrefno) to guarantee
     * reliable history loading when launched from the Patient Masterlist modal.
     */
    public function fetchPatientMedhistory(Request $request) {
        $pincode = $request->input('pincode');
        $pxrefno = $request->input('pxrefno');
        $consultationrefno = $request->input('consultationrefno');

        $query = ConsultationModel::query();
        if (!empty($pincode) && $pincode !== 'undefined') {
            $query->where('pincode', $pincode);
        } elseif (!empty($pxrefno) && $pxrefno !== 'undefined') {
            $query->where('pxrefno', $pxrefno);
        } elseif (!empty($consultationrefno) && $consultationrefno !== 'undefined') {
            $px = ConsultationModel::where('consultationrefno', $consultationrefno)->value('pxrefno');
            if ($px) {
                $query->where('pxrefno', $px);
            } else {
                $query->where('consultationrefno', $consultationrefno);
            }
        }

        $history = $query->select([
                'consultationrefno',
                'pxrefno',
                'pincode',
                'photo_path',
                'consultation_date',
                'reasonforconsultation',
                'status',
                'recordedby',
                'recordeddate'
            ])
            ->orderBy('id', 'desc')
            ->get();

        $history->transform(function ($record) {
            if ($record->photo_path) {
                $filename = basename($record->photo_path);
                $record->photo_path = url('/patient/photo/' . $filename);
            }

            return $record;
        });

        return response()->json([
            'success' => true,
            'history' => $history,
            'data' => $history
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
     * and HMO channels into the pxsettlements table. Synchronizes consultation patient and doctor metadata,
     * generates a unique transaction reference number (TRX...), and records audit logs.
     */
    public function saveSettlements(Request $request) {
        $consultation = ConsultationModel::where('consultationrefno', $request->sett_consultationrefno)->first();

        $record = SettlementsModel::updateOrCreate([
            'consultationrefno' => $request->sett_consultationrefno
        ], [
            'pincode' => $consultation->pincode ?? $request->pincode ?? null,
            'docrefno' => $consultation->docrefno ?? null,
            'docname' => $consultation->docname ?? null,
            'total_gross' => (float) ($request->total ?? 0),
            'net_payable' => (float) ($request->total ?? 0),
            'payment_cash' => (float) ($request->cash ?: 0),
            'payment_card' => (float) ($request->cta ?: 0),
            'cta_type' => $request->cta_type ?? $request->card_type,
            'less_hmo' => (float) ($request->hmo ?: 0),
            'hmo_type' => $request->hmo_type,
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
            // Detailed Comment: Log patient billing settlement save
            Log::info('Consultation settlement saved', [
                'consultationrefno' => $request->sett_consultationrefno,
                'total' => $request->total,
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
     * Detailed Comment: Fetches available HMO entities for the queue settlement modal.
     * Queries by dw_clientcode with fallback to application configuration and all active
     * HMO records with non-empty names, ensuring the HMO selection dropdown is always populated.
     */
    public function fetchHmo()
    {
        $clientCode = session()->get('clientcode') ?? config('app.clientcode') ?? env('CLIENT_CODE', '122377');

        $query = HMOModel::select(['hmocode', 'hmoname'])
            ->whereNotNull('hmoname')
            ->where('hmoname', '!=', '');

        if ($clientCode) {
            $hmo = (clone $query)->where('dw_clientcode', $clientCode)->get();
            if ($hmo->isNotEmpty()) {
                return response()->json(['hmo' => $hmo]);
            }
        }

        $hmo = $query->get();
        return response()->json(['hmo' => $hmo]);
    }
}
