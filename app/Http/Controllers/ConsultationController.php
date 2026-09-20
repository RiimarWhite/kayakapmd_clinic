<?php

namespace App\Http\Controllers;

use App\Models\HMOModel;
use App\Models\PatientMasterlist;
use App\Models\PCBModel;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use App\Models\ConsultationAnswerModel;
use App\Models\DoctorsProfileModel;
use App\Models\ConsultationModel;
use Illuminate\Support\Facades\Log;

class ConsultationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'consultation_refno' => 'required|unique:pxwalkinconsultation,consultationrefno',
            'patient_id_no' => 'required|unique:pxwalkinconsultation,pxrefno',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'sex' => 'required',
            'birthdate' => 'required|date',
            'cellphone_number' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'reason_for_consultation' => 'required',
        ]);

        ConsultationModel::create([
            'consultationrefno' => $request->consultation_refno,
            'pxrefno' => $request->patient_id_no,

            'patientname' => $request->first_name,
            'pxmidname' => $request->middle_name,
            'pxlastname' => $request->last_name,
            'suffix' => $request->suffix,

            'gender' => $request->sex,
            'birthday' => $request->birthdate,
            'age' => $request->age,

            'address' => $request->address,
            'emailaddress' => $request->email,
            'mobilenumber' => $request->cellphone_number,

            'reasonforconsultation' => $request->reason_for_consultation,

            'height' => $request->height,
            'hunit' => $request->h_unit,
            'weight' => $request->weight,
            'wunit' => $request->w_unit,
            'temp' => $request->temperature,
            'tempunit' => $request->t_unit,

            'bpnumerator' => $request->bp_numerator,
            'bpdenominator' => $request->bp_denominator,
            'respiratoryrate' => $request->respiratory_rate,
            'pulserate' => $request->pulse_rate,
        ]);

        return back()->with('success', 'Consultation successfully saved.');
    }

    public function fetchConsultationPatients(Request $request)
    {
        $data = ConsultationModel::where(['docrefno' => $request->docrefno])->get();

        return response()->json(['patients' => $data]);
    }

    public function fetchConsultationPatientsQueue(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $query = ConsultationModel::where(['docrefno' => $request->docrefno])
            ->whereDate('consultation_date', $request->consuldate)
            ->whereTime('consultation_date', $request->consultime)
            ->select([
                'consultationrefno',
                'pxrefno',
                'patientname',
                'queueno',
                'status'
            ]);

        $totalRecords = $query->count();
        $filteredRecords = $query->count();

        $data = $query->orderBy('queueno')
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Detailed Comment: Fetches unscheduled patient consultations for the secretary queue.
     * Excludes patients who already possess a valid scheduled consultation in pxwalkinconsultation
     * (defined as a non-null, non-dummy consultation_date), prevents duplicate patient rows
     * via unique patient reference grouping, and accurately reflects total and filtered pagination counts.
     */
    public function fetchConsultationPatientsUnsched(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');

        // Identify all patient reference numbers who currently have a scheduled consultation
        $scheduledPxrefnos = ConsultationModel::whereNotNull('consultation_date')
            ->whereNotIn('consultation_date', ['1901-01-01 00:00:00', '0000-00-00 00:00:00', ''])
            ->pluck('pxrefno')
            ->filter()
            ->unique();

        $baseQuery = ConsultationModel::where('status', 'UNSCHEDULED')
            ->where(function ($q) {
                $q->whereNull('consultation_date')
                  ->orWhereIn('consultation_date', ['1901-01-01 00:00:00', '0000-00-00 00:00:00', '']);
            })
            ->whereNotIn('pxrefno', $scheduledPxrefnos);

        $totalRecords = (clone $baseQuery)->distinct('pxrefno')->count('pxrefno');

        $query = clone $baseQuery;

        if (!empty($search)) {
            $query->where('patientname', 'LIKE', "%{$search}%");
        }

        $filteredRecords = (clone $query)->distinct('pxrefno')->count('pxrefno');

        $data = $query->groupBy('pxrefno', 'patientname', 'status')
            ->offset($start)
            ->limit($length)
            ->get([
                'patientname',
                'pxrefno',
                'status'
            ]);

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function addPatientRecord(Request $request)
    {
        $patientCode = $this->generatePatientCode();

        // Create patient record into PatientMasterlist
        $record = PatientMasterlist::create([
            'pxrefno' => $patientCode,
            'pincode' => 'PIN' . Carbon::now()->year . '-' . str_pad(PatientMasterlist::count(), 5, '0', STR_PAD_LEFT),
            'patientname' => $request->pPatientLname . ', ' . $request->pPatientFname . ' ' . $request->pPatientMname . ' ' . $request->pPatientExtname,
            'pxfirstname' => $request->pPatientFname,
            'pxmidname' => $request->pPatientMname,
            'pxlastname' => $request->pPatientLname,
            'pxsuffix' => $request->pPatientExtname,
            'gender' => $request->pPatientSex,
            'birthday' => $request->pPatientDob,
            'age' => Carbon::parse($request->pPatientDob)->age,
            'mobilenumber' => $request->pPatientMobileNo,
            'emailaddress' => $request->email,
            'address' => $request->address
        ]);

        $consultation = ConsultationModel::create([
            'pxrefno' => $patientCode,
            'pincode' => 'PIN' . Carbon::now()->year . '-' . str_pad(PatientMasterlist::count(), 5, '0', STR_PAD_LEFT),
            'caseno' => $this->generateCaseCode(),
            'patientname' => $record->patientname,
            'pxfirstname' => $record->pxfirstname,
            'pxmidname' => $record->pxmidname,
            'pxlastname' => $record->pxlastname,
            'pxsuffix' => $record->pxsuffix,
            'gender' => $record->gender == 'MALE' ? 'M' : 'F',
            'birthday' => $record->birthday,
            'age' => $record->age,
            'mobilenumber' => $record->mobilenumber,
            'emailaddress' => $record->emailaddress,
            'status' => 'UNSCHEDULED'
        ]);

        if ($record && $consultation) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function updatePatientRecord(Request $request)
    {
        // 1. Get Doctor details
        $doctor = DoctorsProfileModel::where('doccode', $request->doccode)->first();

        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor profile not found'], 404);
        }

        // 2. Calculate Age
        $age = 0;
        if ($request->birthday) {
            $age = Carbon::parse($request->birthday)->age;
        }

        // 3. Find the LATEST record for this patient using ID instead of created_at
        $latestRecord = ConsultationModel::where('pincode', $request->pincode)
            ->orderBy('id', 'desc')
            ->first();

        // Data to be saved (shared between update and create)
        $data = [
            'docrefno' => $doctor->docrefno,
            'doccoaOPD' => $doctor->coaOPD,
            'doccode' => $request->doccode,
            'memPin' => $request->memPin,
            'patientname' => $request->patientname,
            'pxmidname' => $request->pxmidname,
            'pxlastname' => $request->pxlastname,
            'pxsuffix' => $request->pxsuffix,
            'gender' => $request->gender,
            'birthday' => $request->birthday,
            'age' => $age,
            'memFname' => $request->memFname,
            'memMname' => $request->memMname,
            'memLname' => $request->memLname,
            'memExtname' => $request->memExtname,
            'memDob' => $request->memDob,
            'mobilenumber' => $request->mobilenumber,
            'emailaddress' => $request->emailaddress,
            'address' => $request->address,
        ];

        // 4. Logic Check: Update if status is UNSCHEDULED, otherwise Create New
        if ($latestRecord && $latestRecord->status === 'UNSCHEDULED') {
            // Update existing latest record
            $latestRecord->update($data);
            $message = 'Existing unscheduled consultation updated successfully';
        } else {
            // Add fields specific to a new entry
            $data['pincode'] = $request->pincode;
            $data['casecode'] = $this->generateCaseCode();
            $data['status'] = 'UNSCHEDULED';

            ConsultationModel::create($data);
            $message = 'New consultation entry created successfully';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    // private function generatePxReference()
    // {
    //     return 'PX' . Carbon::now()->format('mdYHis');
    // }

    public function fetchPxMasterlist(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');

        // Detailed Comment: Build query joining pxmasterlist with walk-in consultations (c).
        // Apply doctor filter directly to base query to guarantee accurate pagination counters.
        $baseQuery = DB::table('pxmasterlist')
            ->join('pxwalkinconsultation as c', 'pxmasterlist.pxrefno', '=', 'c.pxrefno')
            ->when($request->filled('docrefno'), function ($q) use ($request) {
                return $q->where('c.docrefno', $request->docrefno);
            });

        $recordsTotal = (clone $baseQuery)->count();

        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('pxmasterlist.patientname', 'like', "%{$search}%")
                    ->orWhere('pxmasterlist.pxfirstname', 'like', "%{$search}%")
                    ->orWhere('pxmasterlist.pxmidname', 'like', "%{$search}%")
                    ->orWhere('pxmasterlist.pxlastname', 'like', "%{$search}%")
                    ->orWhere('pxmasterlist.pincode', 'like', "%{$search}%")
                    ->orWhere('c.caseno', 'like', "%{$search}%")
                    ->orWhere('c.consultationrefno', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        // Detailed Comment: Fix SQLSTATE[42S22] 1054 'Unknown column pxmasterlist.casecode'.
        // The pxmasterlist table has no casecode column. The case/consultation transaction code
        // is stored in c.caseno / c.consultationrefno, aliased as casecode for UI compatibility.
        $data = $baseQuery
            ->select([
                'pxmasterlist.pxrefno',
                DB::raw("COALESCE(NULLIF(c.caseno, ''), NULLIF(c.consultationrefno, ''), pxmasterlist.pxrefno) as casecode"),
                'c.caseno',
                'pxmasterlist.pincode',
                'pxmasterlist.patientname',
                'pxmasterlist.pxfirstname',
                'pxmasterlist.pxmidname',
                'pxmasterlist.pxlastname',
                'pxmasterlist.pxsuffix',
                'c.status',
                'pxmasterlist.mobilenumber',
                'pxmasterlist.emailaddress',
                'c.consultationrefno',
                'c.consultation_date',
                'c.docrefno',
                'c.photo_path'
            ])
            ->orderByDesc('c.consultation_date')
            ->skip($start)
            ->take($length)
            ->get();

        $data->transform(function ($patient) {
            if ($patient->photo_path) {
                $filename = basename($patient->photo_path);
                $patient->photo_path = url('/patient/photo/' . $filename);
            }
            return $patient;
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function fetchPatientMasterlist(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');

        // Get one copy of patients under a doctor
        $latestRecords = ConsultationModel::selectRaw('pincode, MAX(recordeddate) as latest_date')
            ->where(['docrefno' => $request->docrefno])
            ->groupBy('pincode');

        $query = ConsultationModel::from('pxwalkinconsultation as consultations')
            ->joinSub($latestRecords, 'latest', function ($join) {
                $join->on('consultations.pincode', '=', 'latest.pincode')
                    ->on('consultations.recordeddate', '=', 'latest.latest_date');
            })
            ->where('consultations.docrefno', $request->docrefno)
            // Detailed Comment: Select attributes with aliases for nonexistent columns (doccode, casecode, pinno, isMember, memPin, address)
            ->select([
                'consultations.docrefno',
                'consultations.doccoaOPD',
                'consultations.docrefno as doccode',
                'consultations.pincode',
                DB::raw("COALESCE(NULLIF(consultations.caseno, ''), NULLIF(consultations.consultationrefno, ''), consultations.pxrefno) as casecode"),
                'consultations.phic_pin as pinno',
                'consultations.caseno',
                DB::raw("0 as isMember"),
                DB::raw("'' as memPin"),
                'consultations.patientname',
                'consultations.pxmidname',
                'consultations.pxlastname',
                'consultations.pxsuffix',
                'consultations.gender',
                'consultations.birthday',
                'consultations.age',
                'consultations.mobilenumber',
                'consultations.emailaddress',
                DB::raw("'' as address"),
                'consultations.photo_path',
                'consultations.recordeddate'
            ]);

        // Narrow down records by search if not empty
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('consultations.patientname', 'LIKE', "%{$search}%")
                    ->orWhere('consultations.pxlastname', 'LIKE', "%{$search}%");
            });
        }

        // Get unique record count
        $totalRecords = ConsultationModel::where(['docrefno' => $request->docrefno])->distinct('pincode')->count();
        $filteredRecords = $query->count();

        $patients = $query->orderBy('consultations.recordeddate', 'DESC')->offset($start)->limit($length)->get();
        $patients->transform(function ($patient) {
            // Get patient photo by GET
            if ($patient->photo_path) {
                $filename = basename($patient->photo_path);
                $patient->photo_path = url('/patient/photo/' . $filename);
            }

            return $patient;
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'patients' => $patients
        ]);
    }

    public function fetchConsultation(Request $request)
    {
        try {
            // Detailed Comment: Support multi-key patient consultation lookup with graceful fallback to prevent 500/404 on import
            $consultationrefno = $request->input('consultationrefno');
            $pxrefno = $request->input('pxrefno');
            $casecode = $request->input('casecode');

            $query = ConsultationModel::query();

            if (!empty($consultationrefno) && $consultationrefno !== 'undefined') {
                $query->where('consultationrefno', $consultationrefno);
            } elseif (!empty($pxrefno) && $pxrefno !== 'undefined') {
                $query->where('pxrefno', $pxrefno)->orderBy('id', 'desc');
            } elseif (!empty($casecode) && $casecode !== 'undefined') {
                $query->where(function ($q) use ($casecode) {
                    $q->where('consultationrefno', $casecode)
                      ->orWhere('caseno', $casecode)
                      ->orWhere('pxrefno', $casecode);
                })->orderBy('id', 'desc');
            }

            $consultation = $query->first();

            // Detailed Comment: If no walk-in consultation exists, fallback to pxmasterlist to allow pre-filling import form
            if (!$consultation && (!empty($pxrefno) || !empty($casecode))) {
                $lookupKey = (!empty($pxrefno) && $pxrefno !== 'undefined') ? $pxrefno : $casecode;
                // Detailed Comment: pxmasterlist does not have a casecode column; match on pxrefno or pincode
                $pxMaster = PatientMasterlist::where('pxrefno', $lookupKey)
                    ->orWhere('pincode', $lookupKey)
                    ->first();

                if ($pxMaster) {
                    $consultation = new ConsultationModel([
                        'pxrefno' => $pxMaster->pxrefno,
                        'patientname' => $pxMaster->patientname ?: trim($pxMaster->pxfirstname . ' ' . $pxMaster->pxlastname),
                        'pxfirstname' => $pxMaster->pxfirstname,
                        'pxmidname' => $pxMaster->pxmidname,
                        'pxlastname' => $pxMaster->pxlastname,
                        'pxsuffix' => $pxMaster->pxsuffix,
                        'pincode' => $pxMaster->pincode,
                        'bday' => $pxMaster->bday,
                        'sex' => $pxMaster->gender ?: $pxMaster->sex,
                        'adrs' => $pxMaster->adrs,
                        'cellno' => $pxMaster->mobilenumber ?: $pxMaster->cellno,
                        'emailadd' => $pxMaster->emailaddress ?: $pxMaster->emailadd,
                        'status' => 'UNSCHEDULED',
                        'consultation_date' => null
                    ]);
                }
            }

            if (!$consultation) {
                return response()->json(['success' => false, 'message' => 'No record found'], 404);
            }

            if ($consultation->photo_path) {
                $filename = basename($consultation->photo_path);
                $consultation->photo_path = url('/patient/photo/' . $filename);
            }

            $answers = !empty($consultation->consultationrefno)
                ? ConsultationAnswerModel::where(['pxconsultationrefno' => $consultation->consultationrefno])->get()
                : collect();

            return response()->json([
                'success' => true,
                'patient' => $consultation,
                'answers' => $answers
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in fetchConsultation: ' . $e->getMessage(), [
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve consultation details.'
            ], 500);
        }
    }

    public function fetchPatientPhoto($filename)
    {
        if ($filename === "blank_photo.png")
            return response()->file(public_path('images/blank_photo.png'));

        $path = storage_path('app/private/patient_photo/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    /**
     * Save a walk-in consultation record.
     * Correctly handles secretary and admin authentication guards, populates required caseno,
     * default finadiagnosis, and matches database data dictionary.
     */
    public function saveConsultation(Request $request)
    {
        $doctor = DoctorsProfileModel::where(['docrefno' => $request->docrefno])->first();
        $secretary = auth()->guard('secretary')->user();
        $admin = auth()->guard('admin')->user();
        $currentUser = $secretary ?: ($admin ?: auth()->user());
        $secrefno = $secretary ? $secretary->secrefno : ($admin ? ($admin->adminrefno ?: 'ADM-' . $admin->id) : null);
        $recordedby = $currentUser ? ($currentUser->username ?: 'system') : 'system';

        $schedDate = $request->filled('sched_date') ? $request->sched_date : now()->toDateString();
        $rawSchedTime = $request->input('sched_time');
        $schedTime = ($rawSchedTime && strtotime($rawSchedTime) !== false) ? $rawSchedTime : now()->format('H:i:s');

        $queueCount = ConsultationModel::where(['docrefno' => $request->docrefno])
            ->whereDate('consultation_date', Carbon::parse($schedDate))
            ->whereTime('consultation_date', Carbon::parse($schedTime))
            ->count();

        $queueno = str_pad($queueCount + 1, 3, '0', STR_PAD_LEFT);

        $fullName = array_filter([
            $request->pxfname,
            $request->pxmname,
            $request->pxlname,
            $request->pxsuffix
        ]);

        $patientName = implode(' ', $fullName);
        $caseNo = $this->generateCaseCode();

        // Prepare base data matching pxwalkinconsultation schema and data dictionary
        $consultationData = [
            'pxrefno' => $request->pxrefno ?? $this->generatePatientCode(),
            'docrefno' => $request->docrefno,
            'docname' => $doctor ? $doctor->docname : '',
            'pincode' => $request->pincode,
            'caseno' => $caseNo,
            'casecode' => $caseNo,
            'doccoaopd' => $doctor->coaOPD ?? '',
            'patientname' => $patientName,
            'pxfirstname' => $request->pxfname,
            'pxmidname' => $request->pxmname,
            'pxlastname' => $request->pxlname,
            'pxsuffix' => $request->pxsuffix,
            'gender' => $request->pxsex,
            'birthday' => $request->pxbday,
            'age' => $request->pxage,
            'mobilenumber' => $request->pxcellnumber,
            'emailaddress' => $request->pxemail,
            'address' => $request->pxaddress,

            'reasonforconsultation' => $request->reason_for_consultation ?? '',
            'finadiagnosis' => '',
            'weight' => $request->weight,
            'wunit' => $request->w_unit,
            'height' => $request->height,
            'hunit' => $request->h_unit,
            'temp' => $request->temperature,
            'tempunit' => $request->t_unit,
            'respiratoryrate' => $request->respiratory_rate,
            'pulserate' => $request->pulse_rate,
            'bpnumerator' => $request->bp_numerator,
            'bpdenominator' => $request->bp_denominator,

            'hmocode' => $request->hmo_input,
            'hmoname' => $request->hmo_name,

            'secrefno' => $secrefno,
            'consultation_date' => Carbon::parse($schedDate . ' ' . $schedTime),
            'requestedby' => $recordedby,
            'requesteddate' => now(),

            'photo_path' => $request->hasFile('patient_photo') ? $request->file('patient_photo')->store('patient_photo', 'private') : (file_exists(public_path('images/blank_photo.png')) ? public_path('images/blank_photo.png') : ''),
            'radiologypath' => $request->hasFile('radiology_file') ? $request->file('radiology_file')->store('radiology_results', 'private') : null,
            'laboratorypath' => $request->hasFile('laboratory_file') ? $request->file('laboratory_file')->store('laboratory_results', 'private') : null,

            'status' => 'PENDING',
            'queueno' => $queueno
        ];

        try {
            $consultation = ConsultationModel::create($consultationData);

            $answers = $request->input('answer', []);
            foreach ($answers as $questionRef => $answer) {
                ConsultationAnswerModel::create([
                    'pxconsultationrefno' => $consultation->consultationrefno,
                    'questionrefno' => $questionRef,
                    'answer' => $answer,
                    'transactedby' => $recordedby,
                    'transacteddate' => now(),
                    'walkinconsuanswerrefno' => now()->format('mdYHis') . 'QA'
                ]);
            }

            Log::info('Consultation record created', [
                'consultationrefno' => $consultation->consultationrefno,
                'docrefno' => $request->docrefno,
                'patientname' => $patientName,
                'recordedby' => $recordedby
            ]);

            return response()->json(['success' => true, 'consultationrefno' => $consultation->consultationrefno]);
        } catch (\Throwable $e) {
            Log::error('Failed to create consultation record', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to save consultation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update consultation record details with safety guards against non-existent records.
     */
    public function updateConsultation(Request $request)
    {
        $doctor = DoctorsProfileModel::where(['docrefno' => $request->docrefno])->first();
        $secretary = auth()->guard('secretary')->user();
        $admin = auth()->guard('admin')->user();
        $currentUser = $secretary ?: ($admin ?: auth()->user());
        $secrefno = $secretary ? $secretary->secrefno : ($admin ? ($admin->adminrefno ?: 'ADM-' . $admin->id) : null);
        $recordedby = $currentUser ? ($currentUser->username ?: 'system') : 'system';

        $record = ConsultationModel::where('consultationrefno', $request->pxconsultationrefno)
            ->orWhere('id', $request->pxconsultationrefno)
            ->first();

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Consultation record not found.'], 404);
        }

        $schedDate = $request->filled('sched_date') ? $request->sched_date : ($record->consultation_date ? Carbon::parse($record->consultation_date)->toDateString() : now()->toDateString());
        $rawSchedTime = $request->input('sched_time');
        $schedTime = ($rawSchedTime && strtotime($rawSchedTime) !== false) ? $rawSchedTime : ($record->consultation_date ? Carbon::parse($record->consultation_date)->format('H:i:s') : now()->format('H:i:s'));

        $queueCount = ConsultationModel::where(['docrefno' => $request->docrefno])
            ->whereDate('consultation_date', Carbon::parse($schedDate)->startOfDay())
            ->whereTime('consultation_date', Carbon::parse($schedTime)->endOfDay())
            ->count();
        $queueno = $record->queueno ?: str_pad($queueCount + 1, 3, '0', STR_PAD_LEFT);

        $fullName = array_filter([
            $request->pxfname,
            $request->pxmname,
            $request->pxlname,
            $request->pxsuffix
        ]);

        $patientName = implode(' ', $fullName);

        $consultationData = [
            'docrefno' => $request->docrefno,
            'docname' => $doctor ? $doctor->docname : ($record->docname ?? ''),
            'pincode' => $request->pincode,
            'doccoaopd' => $doctor->coaOPD ?? ($record->doccoaopd ?? ''),
            'patientname' => $patientName,
            'pxmidname' => $request->pxmname,
            'pxlastname' => $request->pxlname,
            'pxsuffix' => $request->pxsuffix,
            'gender' => $request->pxsex,
            'birthday' => $request->pxbday,
            'age' => $request->pxage,
            'mobilenumber' => $request->pxcellnumber,
            'emailaddress' => $request->pxemail,
            'address' => $request->pxaddress,

            'reasonforconsultation' => $request->reason_for_consultation ?? ($record->reasonforconsultation ?? ''),
            'weight' => $request->weight,
            'wunit' => $request->w_unit,
            'height' => $request->height,
            'hunit' => $request->h_unit,
            'temp' => $request->temperature,
            'tempunit' => $request->t_unit,
            'respiratoryrate' => $request->respiratory_rate,
            'pulserate' => $request->pulse_rate,
            'bpnumerator' => $request->bp_numerator,
            'bpdenominator' => $request->bp_denominator,

            'hmocode' => $request->hmo_input,
            'hmoname' => $request->hmo_name,

            'secrefno' => $secrefno,
            'consultation_date' => Carbon::parse($schedDate . ' ' . $schedTime),

            'photo_path' => $request->hasFile('patient_photo') ? $request->file('patient_photo')->store('patient_photo', 'private') : $record->photo_path,
            'radiologypath' => $request->hasFile('radiologypath') ? $request->file('radiologypath')->store('radiology_results', 'private') : $record->radiologypath,
            'laboratorypath' => $request->hasFile('laboratorypath') ? $request->file('laboratorypath')->store('laboratory_results', 'private') : $record->laboratorypath,

            'status' => $record->status ?: 'PENDING',
            'queueno' => $queueno
        ];

        try {
            $update = $record->update($consultationData);

            $answers = $request->input('answer', []);
            foreach ($answers as $questionRef => $answer) {
                $answerModel = ConsultationAnswerModel::updateOrCreate([
                    'pxconsultationrefno' => $record->consultationrefno,
                    'questionrefno' => $questionRef
                ], [
                    'answer' => $answer,
                    'transactedby' => $recordedby,
                    'transacteddate' => now(),
                ]);

                if ($answerModel->wasRecentlyCreated) {
                    $answerModel->update([
                        'walkinconsuanswerrefno' => now()->format('mdYHis') . 'QA'
                    ]);
                }
            }

            Log::info('Consultation record updated', [
                'consultationrefno' => $record->consultationrefno,
                'updated_by' => $recordedby
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Failed to update consultation record', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to update consultation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update consultation queue status (WAITING, IN_CONSULTATION, COMPLETED, CANCELLED, NO_SHOW, ON_HOLD, SCHEDULED).
     * Flexible lookup matching by consultationrefno, casecode, caseno, or ID.
     */
    public function updateQueueStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:WAITING,IN_CONSULTATION,FOR_BILLING,COMPLETED,CANCELLED,NO_SHOW,ON_HOLD,SCHEDULED',
        ]);

        $code = $request->input('casecode') ?: ($request->input('consultationrefno') ?: $request->input('caseno'));
        if (!$code) {
            return response()->json(['success' => false, 'message' => 'Consultation identifier is required.'], 400);
        }

        $record = ConsultationModel::where('consultationrefno', $code)
            ->orWhere('caseno', $code)
            ->first();

        if (!$record && \Illuminate\Support\Facades\Schema::hasColumn('pxwalkinconsultation', 'casecode')) {
            $record = ConsultationModel::where('casecode', $code)->first();
        }

        if (!$record && is_numeric($code)) {
            $record = ConsultationModel::where('id', $code)->first();
        }

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        $record->status = $request->status;
        if ($request->status === 'COMPLETED') {
            $record->consulted = true;
            $record->consulteddate = now();
        }
        $record->save();

        Log::info('Queue status updated', [
            'consultationrefno' => $record->consultationrefno,
            'status' => $request->status,
            'updated_by' => auth()->user() ? auth()->user()->username : 'system'
        ]);

        return response()->json(['success' => true]);
    }

    public function fetchLatestPatientDetails(Request $request)
    {
        $data = ConsultationModel::where('pincode', $request->pincode)->orderBy('id', 'DESC')->first();

        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return response()->json(['success' => false, 'message' => 'Not found'], 404);
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

    // PhilHealth Profile
    public function fetchHCIProfile()
    {
        $profile = PCBModel::first();
        if ($profile) {
            return $profile;
        }

        return false;
    }

    public function insertHCIProfile(Request $request)
    {
        $profile = $this->fetchHCIProfile();
        if (!$profile) {
            return response()->json(['success' => false]);
        }

        $profile->update([
            'pUsername' => $request->pusername,
            'pPassword' => $request->ppassword,
        ]);
    }

    private function generatePinCode($prevPincode = null)
    {
        // If a previous pincode exists, reuse it; otherwise, generate a new one
        if ($prevPincode) {
            return $prevPincode;
        }

        $profile = $this->fetchHCIProfile();
        $accre = '-';
        if ($profile) {
            $accre = $profile->hciaccreno;
        }

        $now = Carbon::now('Asia/Manila')->format('Ym');

        // Format: T YYYYMM (e.g., T202603)
        return 'T' . $accre . $now;
    }

    private function generateCaseCode()
    {
        // $profile = $this->fetchHCIProfile();
        // $accre = '-';
        // if ($profile) {
        //     $accre = $profile->hciaccreno;
        // }

        // $now = Carbon::now('Asia/Manila')->format('Ym');

        // Format: E YYYYMM (ex. E202603)
        return 'CN' . Carbon::now()->year . '-' . str_pad(PatientMasterlist::count(), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Detailed Comment: Reschedules a patient consultation record.
     * Looks up the record by consultationrefno or caseno (resolving legacy SQL 1054 error on nonexistent 'casecode' column).
     * Calculates the new queueno based on the target doctor and date/time, resets status to WAITING, and updates the timestamp.
     */
    public function reschedulePatient(Request $request)
    {
        try {
            $consultation = ConsultationModel::where('consultationrefno', $request->consultationrefno)
                ->orWhere('caseno', $request->consultationrefno)
                ->first();

            if (!$consultation) {
                Log::warning('Reschedule patient failed: consultation record not found', [
                    'consultationrefno' => $request->consultationrefno
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Consultation record not found.'
                ], 404);
            }

            $docrefno = $request->docrefno ?: $consultation->docrefno;
            $targetDate = $request->date ?: now()->toDateString();
            $targetTime = !empty($request->time) ? $request->time : '00:00:00';

            // Detailed Comment: Parse combined datetime safely using Carbon
            $newConsultDate = Carbon::parse($targetDate . ' ' . $targetTime);

            // Detailed Comment: Compute next queue number for the target doctor and consultation date/time
            $queueQuery = ConsultationModel::where('docrefno', $docrefno)
                ->whereDate('consultation_date', $newConsultDate->toDateString());

            if (!empty($request->time)) {
                $queueQuery->whereTime('consultation_date', $newConsultDate->format('H:i:s'));
            }

            $queueCount = $queueQuery->count();
            $newQueueNo = str_pad($queueCount + 1, 3, '0', STR_PAD_LEFT);

            $consultation->consultation_date = $newConsultDate;
            $consultation->queueno = $newQueueNo;
            $consultation->status = 'WAITING';
            if ($docrefno) {
                $consultation->docrefno = $docrefno;
            }
            $consultation->save();

            Log::info('Patient consultation rescheduled successfully', [
                'consultationrefno' => $consultation->consultationrefno,
                'new_date' => $newConsultDate->toDateTimeString(),
                'new_queueno' => $newQueueNo
            ]);

            return response()->json([
                'success' => true,
                'queueno' => $newQueueNo,
                'consultation_date' => $newConsultDate->toDateTimeString()
            ]);
        } catch (\Throwable $e) {
            Log::error('Reschedule patient failed with exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while rescheduling the patient: ' . $e->getMessage()
            ], 500);
        }
    }

    public function refreshQueue(Request $request)
    {
        $consultations = ConsultationModel::whereDate('consultation_date', $request->date)
            ->whereTime('consultation_date', $request->time)
            ->orderBy('queueno', 'ASC')
            ->get();

        $queueno = 1;

        foreach ($consultations as $consultation) {
            ConsultationModel::where('id', $consultation->id)->update([
                'queueno' => str_pad($queueno, 3, '0', STR_PAD_LEFT)
            ]);
            $queueno++;
        }

        return response()->json(['success' => true]);
    }

    /**
     * Detailed Comment: Fetches available HMO entities for consultations and settlements.
     * Queries by clientcode with defensive fallbacks to application configuration and all active
     * non-empty HMO records so the HMO dropdown is never empty.
     */
    public function fetchHMO()
    {
        $clientCode = session()->get('clientcode') ?? config('app.clientcode') ?? env('CLIENT_CODE', '122377');

        $query = HMOModel::select(['hmocode', 'hmoname'])
            ->whereNotNull('hmoname')
            ->where('hmoname', '!=', '');

        if ($clientCode) {
            $hmo = (clone $query)->where('dw_clientcode', $clientCode)->get();
            if ($hmo->isNotEmpty()) {
                return response()->json(['success' => true, 'hmo' => $hmo]);
            }
        }

        $hmo = $query->get();
        return response()->json(['success' => true, 'hmo' => $hmo]);
    }
}
