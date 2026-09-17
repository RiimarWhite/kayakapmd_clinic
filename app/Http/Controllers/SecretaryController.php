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

    // Schedule-related
    public function fetchSchedules(Request $request)
    {
        $schedules = ScheduleModel::where('docrefno', $request->docrefno)->orderBy('day', 'ASC')->get();

        return response()->json(['success' => true, 'schedules' => $schedules]);
    }

    public function fetchSchedulesSpecific(Request $request)
    {
        $weekday = (new DateTime($request->consuldate))->format('l');
        $schedules = ScheduleModel::where([
            'docrefno' => $request->docrefno,
            'day' => $weekday
        ])->get();

        return response()->json(['schedules' => $schedules]);
    }

    // To get specific schedule by schedrefno
    public function fetchScheduleByRef(Request $request) {
        $sched = ScheduleModel::where(['schedrefno' => $request->schedrefno])->first();

        return response()->json(['success' => true, 'sched' => $sched]);
    }

    public function editSchedule(Request $request) {
        ScheduleModel::where(['schedrefno' => $request->schedrefno])
            ->update([
                'day' => $request->day,
                'start' => $request->start,
                'end' => $request->end,
                'notes' => $request->notes
            ]);

        return response()->json(['success' => true]);
    }

    public function createSchedule(Request $request)
    {
        ScheduleModel::create([
            'schedrefno' => Date::now()->format('mdYHis') . 'SCHED',
            'docrefno' => $request->docrefno,
            'day' => $request->schedule_day,
            'start' => $request->sched_from,
            'end' => $request->sched_to,
            // 'notes' => $request->notes
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteSchedule(Request $request) {
        ScheduleModel::where('schedrefno', $request->schedrefno)->delete();

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

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function fetchPatientMedhistory(Request $request) {
        $history = ConsultationModel::where(['pincode' => $request->pincode])
            ->select([
                'photo_path',
                'consultation_date',
                'reasonforconsultation',
                'status',
                'recordedby',
                'recordeddate'
            ])
            ->get();
        $history->transform(function ($record) {
            if ($record->photo_path) {
                $filename = basename($record->photo_path);
                $record->photo_path = url('/patient/photo/' . $filename);
            }

            return $record;
        });

        if ($history) {
            return response()->json(['history' => $history]);
        }

        return response()->json(['history' => null]);
    }

    // Settlements-related
    public function fetchSettlements(Request $request) {
        $record = SettlementsModel::where(['consultationrefno' => $request->consultationrefno])->first();

        if ($record) {
            return response()->json(['success' => true, 'record' => $record]);
        }

        return response()->json(['success' => false]);
    }

    public function saveSettlements(Request $request) {
        $record = SettlementsModel::updateOrCreate([
            'consultationrefno' => $request->sett_consultationrefno
        ], [
            'transactionrefno' => '',
            'net_total' => $request->total,
            'cash' => $request->cash,
            'cta' => $request->cta,
            'cta_type' => $request->cta_type,
            'hmo' => $request->hmo,
            'hmo_type' => $request->hmo_type
        ]);

        if ($record->wasRecentlyCreated) {
            $record->transactionrefno = 'TRX' . Date::now()->format('mdYHis');
            $record->save();
        }

        if ($record) {
            // Detailed Comment: Log patient billing settlement save
            Log::info('Consultation settlement saved', [
                'consultationrefno' => $request->sett_consultationrefno,
                'total' => $request->total,
                'transactionrefno' => $record->transactionrefno,
                'recorded_by' => auth()->guard('secretary')->check() ? auth()->guard('secretary')->user()->seclname : null,
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function fetchHmo()
    {
        $hmo = HMOModel::select(['hmocode', 'hmoname'])->where(['dw_clientcode' => session()->get('clientcode')])->get();

        return response()->json(['hmo' => $hmo]);
    }
}
