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
use App\Models\Stocks\StocksLedgerModel;
use App\Models\PatientMasterlist;
use App\Models\PxMedicalHistoryModel;
use App\Models\KayakapProfileModel;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $history = $query->select([
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
                'address',
                'photo_path',
                'consultation_date',
                'reasonforconsultation',
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

        $history->transform(function ($record) {
            if ($record->photo_path && !str_contains($record->photo_path, 'blank_photo.png')) {
                $filename = basename($record->photo_path);
                $record->photo_path = url('/patient/photo/' . $filename);
                $record->photo_url = url('/patient/photo/' . $filename);
            } else {
                $record->photo_path = url('/images/blank_photo.png');
                $record->photo_url = url('/images/blank_photo.png');
            }

            // Prescriptions (Rx) from stocks_ledger
            $record->rx_items = StocksLedgerModel::where('px_consultcode_cn', $record->consultationrefno)
                ->where('item_grouping', 'DRUGS AND MEDS')
                ->select(['prodcode', 'item_dscr', 'qty', 'remarks', 'dispensed_flag'])
                ->get();

            // Diagnostic requests from stocks_ledger
            $record->diagnostic_items = StocksLedgerModel::where('px_consultcode_cn', $record->consultationrefno)
                ->whereIn('item_grouping', ['DIAGNOSTIC', 'IMAGING'])
                ->select(['prodcode', 'item_dscr', 'item_grouping', 'remarks'])
                ->get();

            // Charges from stocks_ledger
            $record->charges = StocksLedgerModel::where('px_consultcode_cn', $record->consultationrefno)
                ->select(['id', 'prodcode', 'item_dscr', 'item_grouping', 'qty', 'cost_ave', 'totalamt'])
                ->get();
            $record->charges_total = $record->charges->sum('totalamt');

            return $record;
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsFiltered' => $history->count(),
            'recordsTotal' => $history->count(),
            'success' => true,
            'history' => $history,
            'medhistory' => $history,
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

        if (empty($pxrefno) && !empty($consultationrefno)) {
            $consult = ConsultationModel::where('consultationrefno', $consultationrefno)->first();
            if ($consult) {
                $pxrefno = $consult->pxrefno;
                if (empty($pincode)) $pincode = $consult->pincode;
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
            'data' => $medHistory
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

        // Resolve missing pxrefno from consultation or masterlist
        if (empty($pxrefno) && !empty($consultationrefno)) {
            $consult = ConsultationModel::where('consultationrefno', $consultationrefno)->first();
            if ($consult) {
                $pxrefno = $consult->pxrefno;
                if (empty($pincode)) $pincode = $consult->pincode;
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

